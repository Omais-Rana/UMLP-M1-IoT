package com.example.firelocator;

import android.Manifest;
import android.content.Context;
import android.content.pm.PackageManager;
import android.hardware.Sensor;
import android.hardware.SensorEvent;
import android.hardware.SensorEventListener;
import android.hardware.SensorManager;
import android.location.Location;
import android.os.Bundle;
import android.os.Looper;
import android.telephony.SmsManager;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.camera.core.CameraSelector;
import androidx.camera.core.Preview;
import androidx.camera.lifecycle.ProcessCameraProvider;
import androidx.camera.view.PreviewView;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.lifecycle.LifecycleOwner;

import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationCallback;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationResult;
import com.google.android.gms.location.LocationServices;
import com.google.common.util.concurrent.ListenableFuture;

import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutionException;

public class MainActivity extends AppCompatActivity implements SensorEventListener {

    // --- Permissions ---
    private static final int PERMISSIONS_REQUEST_CODE = 10;
    private String[] allPermissions = {
            Manifest.permission.CAMERA,
            Manifest.permission.ACCESS_FINE_LOCATION,
            Manifest.permission.SEND_SMS
    };

    // --- UI Views ---
    private PreviewView cameraPreviewView;
    private Button sendButton;

    // --- CameraX ---
    private ListenableFuture<ProcessCameraProvider> cameraProviderFuture;

    // --- Location ---
    private FusedLocationProviderClient fusedLocationClient;
    private LocationCallback locationCallback;
    private double currentLatitude = 0.0;
    private double currentLongitude = 0.0;
    private double currentAltitude = 0.0;

    // --- Sensors (for Azimuth and Elevation)  ---
    private SensorManager sensorManager;
    private final float[] accelerometerReading = new float[3];
    private final float[] magnetometerReading = new float[3];
    private final float[] rotationMatrix = new float[9];
    private final float[] orientationAngles = new float[3];
    private double currentAzimuth = 0.0; // Angle from North
    private double currentPitch = 0.0;   // Elevation angle

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        cameraPreviewView = findViewById(R.id.camera_preview);
        sendButton = findViewById(R.id.send_button);

        sensorManager = (SensorManager) getSystemService(Context.SENSOR_SERVICE);

        fusedLocationClient = LocationServices.getFusedLocationProviderClient(this);

        if (allPermissionsGranted()) {
            startAllServices();
        } else {
            ActivityCompat.requestPermissions(this, allPermissions, PERMISSIONS_REQUEST_CODE);
        }

        sendButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                calculateAndSendCoordinates();
            }
        });
    }

    private boolean allPermissionsGranted() {
        for (String permission : allPermissions) {
            if (ContextCompat.checkSelfPermission(this, permission) != PackageManager.PERMISSION_GRANTED) {
                return false;
            }
        }
        return true;
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == PERMISSIONS_REQUEST_CODE) {
            if (allPermissionsGranted()) {
                startAllServices();
            } else {
                Toast.makeText(this, "Permissions not granted. App cannot function.", Toast.LENGTH_LONG).show();
                finish();
            }
        }
    }

    private void startAllServices() {
        startCamera();
        startLocationUpdates();
    }

    // --- CameraX Setup ---
    private void startCamera() {
        cameraProviderFuture = ProcessCameraProvider.getInstance(this);
        cameraProviderFuture.addListener(() -> {
            try {
                ProcessCameraProvider cameraProvider = cameraProviderFuture.get();
                bindCameraPreview(cameraProvider);
            } catch (ExecutionException | InterruptedException e) {
                Toast.makeText(this, "Error starting camera: " + e.getMessage(), Toast.LENGTH_SHORT).show();
            }
        }, ContextCompat.getMainExecutor(this));
    }

    void bindCameraPreview(@NonNull ProcessCameraProvider cameraProvider) {
        Preview preview = new Preview.Builder().build();
        CameraSelector cameraSelector = new CameraSelector.Builder()
                .requireLensFacing(CameraSelector.LENS_FACING_BACK) // Use back camera
                .build();
        preview.setSurfaceProvider(cameraPreviewView.getSurfaceProvider());
        cameraProvider.bindToLifecycle((LifecycleOwner) this, cameraSelector, preview);
    }

    // --- Location & Sensor Setup ---
    private void startLocationUpdates() {
        LocationRequest locationRequest = LocationRequest.create();
        locationRequest.setInterval(5000); // 5 seconds
        locationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);

        locationCallback = new LocationCallback() {
            @Override
            public void onLocationResult(@NonNull LocationResult locationResult) {
                for (Location location : locationResult.getLocations()) {
                    currentLatitude = location.getLatitude();
                    currentLongitude = location.getLongitude();
                    currentAltitude = location.getAltitude();
                    // You can uncomment this to check if GPS is working
                    // Log.d("GPS", "Lat: " + currentLatitude + ", Lon: " + currentLongitude);
                }
            }
        };

        // This check is required by Android
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED
                && ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
            return;
        }
        fusedLocationClient.requestLocationUpdates(locationRequest, locationCallback, Looper.getMainLooper());
    }

    @Override
    protected void onResume() {
        super.onResume();
        // Register sensor listeners
        Sensor accelerometer = sensorManager.getDefaultSensor(Sensor.TYPE_ACCELEROMETER);
        if (accelerometer != null) {
            sensorManager.registerListener(this, accelerometer, SensorManager.SENSOR_DELAY_NORMAL, SensorManager.SENSOR_DELAY_UI);
        }
        Sensor magneticField = sensorManager.getDefaultSensor(Sensor.TYPE_MAGNETIC_FIELD);
        if (magneticField != null) {
            sensorManager.registerListener(this, magneticField, SensorManager.SENSOR_DELAY_NORMAL, SensorManager.SENSOR_DELAY_UI);
        }
    }

    @Override
    protected void onPause() {
        super.onPause();
        // Stop sensors and location to save battery
        sensorManager.unregisterListener(this);
        if (locationCallback != null) {
            fusedLocationClient.removeLocationUpdates(locationCallback);
        }
    }

    @Override
    public void onSensorChanged(SensorEvent event) {
        if (event.sensor.getType() == Sensor.TYPE_ACCELEROMETER) {
            System.arraycopy(event.values, 0, accelerometerReading, 0, accelerometerReading.length);
        } else if (event.sensor.getType() == Sensor.TYPE_MAGNETIC_FIELD) {
            System.arraycopy(event.values, 0, magnetometerReading, 0, magnetometerReading.length);
        }

        // Get rotation matrix
        SensorManager.getRotationMatrix(rotationMatrix, null, accelerometerReading, magnetometerReading);
        // Get orientation
        SensorManager.getOrientation(rotationMatrix, orientationAngles);

        currentAzimuth = Math.toDegrees(orientationAngles[0]); // Convert to degrees
        currentPitch = Math.toDegrees(orientationAngles[1]);   // Convert to degrees

        // Azimuth is 0-360, make sure it's positive
        if (currentAzimuth < 0) {
            currentAzimuth += 360;
        }
    }

    @Override
    public void onAccuracyChanged(Sensor sensor, int accuracy) {
        // Not needed for this app
    }

    // --- The Final Calculation and SMS Logic ---
    private void calculateAndSendCoordinates() {
        if (currentLatitude == 0.0 && currentLongitude == 0.0) {
            Toast.makeText(this, "Waiting for GPS lock...", Toast.LENGTH_SHORT).show();
            return;
        }

        // --- 1. Get all data  ---
        double height = 1.5; // Height of smartphone is 1.5 meters [cite: 6]
        double pitchRadians = Math.toRadians(currentPitch);
        double azimuth = currentAzimuth;

        // --- 2. Calculate distance [cite: 13, 14] ---
        // The PDF formula "distance = height x tan(elevation angle)" [cite: 13]
        // The diagram [cite: 14] shows "elevation angle" (let's call it 'theta') as the angle from the *vertical*.
        // The sensor's "pitch" ('currentPitch') is the angle from the *horizontal*.
        // So, theta = 90 - abs(pitch)
        // We must use Math.abs() because pitch is negative when pointing down.
        double theta = Math.PI / 2.0 - Math.abs(pitchRadians); // 90 degrees in radians - abs(pitch)
        double distance = height * Math.tan(theta);

        // This is a safety check. If you point at the horizon, pitch is ~0, distance is infinite.
        if (Double.isInfinite(distance) || Double.isNaN(distance) || distance > 20000) { // 20km limit
            Toast.makeText(this, "Calculation error. Are you pointing at the horizon?", Toast.LENGTH_LONG).show();
            return;
        }

        // --- 3. Calculate destination coordinates ---
        Point myPosition = new Point(currentLatitude, currentLongitude);
        Point firePosition = myPosition.destination(distance, azimuth); // [cite: 51]

        // --- 4. Build and send SMS  ---
        String fireLat = String.format("%.6f", firePosition.lat);
        String fireLon = String.format("%.6f", firePosition.lon);

        String message = "FIRE REPORT:\n" +
                "My Pos: " + String.format("%.6f", currentLatitude) + ", " + String.format("%.6f", currentLongitude) + "\n" +
                "My Altitude: " + String.format("%.1f", currentAltitude) + "m\n" +
                "Azimuth: " + String.format("%.1f", azimuth) + "°\n" +
                "Elevation: " + String.format("%.1f", currentPitch) + "°\n" +
                "Est. Distance: " + String.format("%.1f", distance) + "m\n\n" +
                "!!! FIRE COORDINATES !!!\n" +
                "Lat: " + fireLat + "\n" +
                "Lon: " + fireLon;

        // *** REPLACE THIS WITH THE RESCUE TEAM'S PHONE NUMBER ***
        String rescuePhoneNumber = "555-1234";

        try {
            SmsManager smsManager = SmsManager.getDefault();
            // SMS messages have a length limit. We split it if it's too long.
            ArrayList<String> parts = smsManager.divideMessage(message);
            smsManager.sendMultipartTextMessage(rescuePhoneNumber, null, parts, null, null);

            Toast.makeText(this, "Report sent to " + rescuePhoneNumber, Toast.LENGTH_LONG).show();
            Log.d("SMS", "Sent SMS: \n" + message);

        } catch (Exception e) {
            Toast.makeText(this, "SMS failed to send. Check permissions.", Toast.LENGTH_LONG).show();
            Log.e("SMS", "Error sending SMS", e);
        }
    }
}