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
const char* topic_sensor = "home/livingroom/sensor"; // Sending Data
const char* topic_light  = "home/livingroom/light";  // Receiving Commands <--- NEW

// Hardware Settings
#define DHTPIN 19      // KEEPING YOUR WORKING PIN 19
#define DHTTYPE DHT11 
#define LED_PIN 2      // Built-in LED (Blue) <--- NEW

// ==========================================
// 2. OBJECTS & VARIABLES
// ==========================================

WiFiClient espClient;
PubSubClient client(espClient);
DHT dht(DHTPIN, DHTTYPE);

unsigned long lastMsg = 0;
const long interval = 5000;

// ==========================================
// 3. NEW: CALLBACK FUNCTION (Listens for Commands)
// ==========================================
void callback(char* topic, byte* message, unsigned int length) {
  Serial.print("Message arrived on topic: ");
  Serial.print(topic);
  Serial.print(". Message: ");
  
  String messageTemp;
  for (int i = 0; i < length; i++) {
    Serial.print((char)message[i]);
    messageTemp += (char)message[i];
  }
  Serial.println();

  // If the message is for the light, turn it ON or OFF
  if (String(topic) == topic_light) {
    if(messageTemp == "on"){
      Serial.println("Turn LED ON");
      digitalWrite(LED_PIN, HIGH);
    }
    else if(messageTemp == "off"){
      Serial.println("Turn LED OFF");
      digitalWrite(LED_PIN, LOW);
    }
  }
}

// ==========================================
// 4. SETUP HELPERS
// ==========================================

void setup_wifi() {
  delay(10);
  Serial.println();
  Serial.print("Connecting to WiFi: ");
  Serial.println(ssid);

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWiFi connected!");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    if (client.connect("ESP32_LivingRoom")) { 
      Serial.println("connected!");
      // *** SUBSCRIBE TO LIGHT TOPIC ***
      client.subscribe(topic_light); 
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" - try again in 5s");
      delay(5000);
    }
  }
}

// ==========================================
// 5. SETUP
// ==========================================

void setup() {
  Serial.begin(115200);
  
  pinMode(LED_PIN, OUTPUT); // Configure LED pin <--- NEW
  
  dht.begin();
  Serial.println("DHT Sensor Started");
  
  setup_wifi();
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback); // Register the listener <--- NEW
}

// ==========================================
// 6. MAIN LOOP
// ==========================================

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop(); // Checks for incoming MQTT messages

  unsigned long now = millis();
  if (now - lastMsg > interval) {
    lastMsg = now;

    // --- REAL SENSOR READING ---
    float humidity = dht.readHumidity();
    float temp = dht.readTemperature(); 

    // Debug print
    if (isnan(humidity) || isnan(temp)) {
      Serial.println("Failed to read from DHT sensor!");
      return; 
    }

    // --- CREATE JSON PAYLOAD ---
    String payload = "{\"temp\":";
    payload += String(temp, 2);
    payload += ",\"humidity\":";
    payload += String(humidity, 2);
    payload += "}";

    // --- PUBLISH ---
    Serial.print("Publishing: ");
    Serial.println(payload);

    client.publish(topic_sensor, payload.c_str());
  }
}