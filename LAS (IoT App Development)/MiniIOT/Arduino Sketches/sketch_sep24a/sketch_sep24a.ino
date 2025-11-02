/*
  Simple Arduino Nano 33 IoT Accelerometer
  Sends only accelerometer data to your dashboard
*/

#include <WiFiNINA.h>
#include <Arduino_LSM6DS3.h>

// WiFi credentials
const char* ssid = "OwaisA32";
const char* password = "12211221";

// Server configuration
const char* serverHost = "10.228.51.244";
const int serverPort = 9000;

WiFiClient tcpClient;

void setup() {
  Serial.begin(115200);
  
  // Initialize accelerometer
  if (!IMU.begin()) {
    Serial.println("Failed to initialize accelerometer!");
    while (1);
  }
  
  // Connect to WiFi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected!");
}

void loop() {
  float x, y, z;
  
  // Read accelerometer
  if (IMU.accelerationAvailable()) {
    IMU.readAcceleration(x, y, z);
    
    // Send to TCP server
    if (tcpClient.connect(serverHost, serverPort)) {
      String data = "{\"x\":" + String(x, 3) + 
                    ",\"y\":" + String(y, 3) + ",\"z\":" + String(z, 3) + "}";
      
      tcpClient.println(data);
      tcpClient.stop();
      
      Serial.println("Data sent: " + data);
    }
  }
  
  delay(1000); // Send every 1 second
}
