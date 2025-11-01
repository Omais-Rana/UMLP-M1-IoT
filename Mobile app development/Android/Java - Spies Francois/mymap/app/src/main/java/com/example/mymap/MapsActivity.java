package com.example.mymap;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;

import android.Manifest;
import android.content.pm.PackageManager;
import android.os.Bundle;
import android.widget.Toast;

import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MarkerOptions;
import com.google.android.gms.maps.model.Polyline;
import com.google.android.gms.maps.model.PolylineOptions;

public class MapsActivity extends AppCompatActivity implements OnMapReadyCallback {

    private GoogleMap mMap;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_maps);

        SupportMapFragment mapFragment = (SupportMapFragment) getSupportFragmentManager()
                .findFragmentById(R.id.map);
        assert mapFragment != null;
        mapFragment.getMapAsync(this);
    }

    @Override
    public void onMapReady(@NonNull GoogleMap googleMap) {
        mMap = googleMap;

        // Coordinates
        LatLng belfort = new LatLng(47.64, 6.84);
        LatLng montbeliard = new LatLng(47.5, 6.8);

        // Add markers
        mMap.addMarker(new MarkerOptions().position(belfort).title("Belfort"));
        mMap.addMarker(new MarkerOptions().position(montbeliard).title("Montbéliard"));

        // Draw line (polyline) from Montbéliard to Belfort
        Polyline line = mMap.addPolyline(new PolylineOptions()
                .add(montbeliard, belfort)
                .width(5f)
                .color(0xFFFF0000) // Red color
        );

        // Move camera to show both points
        LatLng centerPoint = new LatLng((belfort.latitude + montbeliard.latitude) / 2,
                (belfort.longitude + montbeliard.longitude) / 2);
        mMap.moveCamera(CameraUpdateFactory.newLatLngZoom(centerPoint, 10f));
    }
}
