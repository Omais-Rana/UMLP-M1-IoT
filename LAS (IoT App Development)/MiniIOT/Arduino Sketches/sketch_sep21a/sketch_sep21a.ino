#include <WiFi.h>
#include <DHT.h>

// WiFi credentials
const char* ssid = "OwaisA32";
const char* password = "12211221";

// DHT sensor settings
#define DHT_PIN 19
#define DHT_TYPE DHT11
DHT dht(DHT_PIN, DHT_TYPE);

// TCP server settings
const char* tcpServer = "10.228.51.244";  // Laravel server IP
const int tcpPort = 9000;

WiFiClient client;

void setup() {
  Serial.begin(115200);
  
  // Initialize DHT sensor
  dht.begin();
  
  // Connect to WiFi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println();
  Serial.print("Connected! IP address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    sendSensorData();
  } else {
    Serial.println("WiFi disconnected");
  }
  
  delay(1000); // Send data every second
}

void sendSensorData() {
  // Read sensor data
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();
  
  // Check if readings are valid
  if (isnan(temperature) || isnan(humidity)) {
    Serial.println("Failed to read from DHT sensor!");
    return;
  }
  
  // Connect to TCP server
  if (client.connect(tcpServer, tcpPort)) {
    // Create JSON payload
    String jsonData = "{";
    jsonData += "\"temperature\":" + String(temperature, 2) + ",";
    jsonData += "\"humidity\":" + String(humidity, 2) + ",";
    jsonData += "\"device_type\":\"esp32\",";
    jsonData += "}";
    
    // Send data
    client.print(jsonData);
    client.stop();
    
    Serial.println("Data sent - Temp: " + String(temperature) + "°C, Humidity: " + String(humidity) + "%");
  } else {
    Serial.println("Connection to TCP server failed");
  }
}
