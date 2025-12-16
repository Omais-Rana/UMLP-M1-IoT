#include <WiFi.h>
#include <PubSubClient.h>
#include <DHT.h>

// ==========================================
// 1. CONFIGURATION
// ==========================================

// WiFi credentials
const char* ssid = "Redmi12";
const char* password = "11111111";

// MQTT Broker settings
const char* mqtt_server = "10.122.127.244"; 
const int mqtt_port = 1883;

// Topics
const char* topic_sensor = "home/livingroom/sensor"; // Sends Data
const char* topic_light  = "home/livingroom/light";  // Manual Dashboard Switch
const char* topic_ac     = "home/livingroom/ac";     // Auto AC Control  <--- NEW
const char* topic_humid  = "home/livingroom/humid";  // Auto Mold Alert  <--- NEW

// Hardware Settings
#define DHTPIN 19       
#define DHTTYPE DHT11 

// LED Pin Definitions
#define LED_MAIN_PIN 2  // Built-in LED (Manual Control)
#define LED_AC_PIN   21  // External LED 1 (Temp Automation) <--- NEW
#define LED_MOLD_PIN 18  // External LED 2 (Humid Automation) <--- NEW

// ==========================================
// 2. OBJECTS & VARIABLES
// ==========================================

WiFiClient espClient;
PubSubClient client(espClient);
DHT dht(DHTPIN, DHTTYPE);

unsigned long lastMsg = 0;
const long interval = 5000;

// ==========================================
// 3. CALLBACK FUNCTION (The Brains)
// ==========================================
void callback(char* topic, byte* message, unsigned int length) {
  String messageTemp;
  for (int i = 0; i < length; i++) {
    messageTemp += (char)message[i];
  }
  
  // LOGIC 1: Manual Light (Dashboard) -> Built-in LED
  if (String(topic) == topic_light) {
    if(messageTemp == "on"){
      digitalWrite(LED_MAIN_PIN, HIGH);
    }
    else if(messageTemp == "off"){
      digitalWrite(LED_MAIN_PIN, LOW);
    }
  }

  // LOGIC 2: Auto AC (Node-RED) -> External LED on Pin 4
  if (String(topic) == topic_ac) {
    if(messageTemp == "on"){
      digitalWrite(LED_AC_PIN, HIGH);
    }
    else if(messageTemp == "off"){
      digitalWrite(LED_AC_PIN, LOW);
    }
  }

  // LOGIC 3: Mold Alert (Node-RED) -> External LED on Pin 5
  if (String(topic) == topic_humid) {
    if(messageTemp == "on"){
      digitalWrite(LED_MOLD_PIN, HIGH);
    }
    else if(messageTemp == "off"){
      digitalWrite(LED_MOLD_PIN, LOW);
    }
  }
}

// ==========================================
// 4. SETUP HELPERS
// ==========================================

void setup_wifi() {
  delay(10);
  Serial.print("Connecting to WiFi: ");
  Serial.println(ssid);

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected!");
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    if (client.connect("ESP32_LivingRoom")) { 
      Serial.println("connected!");
      
      // *** SUBSCRIBE TO ALL 3 TOPICS ***
      client.subscribe(topic_light); 
      client.subscribe(topic_ac); 
      client.subscribe(topic_humid); 
      
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      delay(5000);
    }
  }
}

// ==========================================
// 5. SETUP
// ==========================================

void setup() {
  Serial.begin(115200);
  
  // Configure ALL LED pins
  pinMode(LED_MAIN_PIN, OUTPUT);
  pinMode(LED_AC_PIN, OUTPUT);
  pinMode(LED_MOLD_PIN, OUTPUT);
  
  dht.begin();
  
  setup_wifi();
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback); 
}

// ==========================================
// 6. MAIN LOOP
// ==========================================

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop(); 

  unsigned long now = millis();
  if (now - lastMsg > interval) {
    lastMsg = now;

    // Read Sensors
    float humidity = dht.readHumidity();
    float temp = dht.readTemperature(); 

    if (isnan(humidity) || isnan(temp)) {
      Serial.println("Failed to read from DHT sensor!");
      return; 
    }

    // JSON Payload
    String payload = "{\"temp\":";
    payload += String(temp, 2);
    payload += ",\"humidity\":";
    payload += String(humidity, 2);
    payload += "}";

    // Publish
    Serial.print("Publishing: ");
    Serial.println(payload);

    client.publish(topic_sensor, payload.c_str());
  }
}