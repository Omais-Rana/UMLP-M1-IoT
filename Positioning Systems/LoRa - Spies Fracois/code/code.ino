#include <SPI.h>
#include <RH_RF95.h>
#include <math.h>

#define RFM95_CS 8
#define RFM95_RST 4
#define RFM95_INT 3

RH_RF95 rf95(RFM95_CS, RFM95_INT);

// Calibration
const float A_reference_rssi = -55.0; 
const float n_path_loss = 3.0;        

// Variables
float d[3] = {0.0, 0.0, 0.0}; 
float bx[3] = {0.0, 8.0, 0.0}; 
float by[3] = {0.0, 0.0, 8.0}; 
bool received[3] = {false, false, false}; 

void setup() {
  Serial.begin(115200);
  while (!Serial) { delay(10); }

  if (!rf95.init()) {
    Serial.println("LoRa init failed");
    while (1);
  }
  
  // Configure LoRa
  rf95.setModemConfig(RH_RF95::Bw125Cr45Sf128);
  rf95.setFrequency(868.1); 
  rf95.setTxPower(23, false); 
  
  Serial.println("--- SYSTEM START ---");
  Serial.println("(Bitrate test skipped for cleaner map output)");
}

void loop() {
  uint8_t buf[255];
  uint8_t len = sizeof(buf);

  if (rf95.waitAvailableTimeout(1000)) { 
    if (rf95.recv(buf, &len)) {
      buf[len] = 0; 
      
      char* ptr = (char*)buf;
      int commaCount = 0;
      
      // Skip the first two commas
      while (*ptr && commaCount < 2) {
        if (*ptr == ',') commaCount++;
        ptr++;
      }
      
      int id = -1;
      float rx_x = 0;
      float rx_y = 0;
      
      int items = sscanf(ptr, "%d,%f,%f", &id, &rx_x, &rx_y);
      
      if (items >= 1 && id >= 0 && id <= 2) {
        
        if (items == 3) {
          bx[id] = rx_x;
          by[id] = rx_y;
        }

        float rssi = (float)rf95.lastRssi();
        float exponent = (A_reference_rssi - rssi) / (10.0 * n_path_loss);
        float distance = pow(10.0, exponent);
        
        if (distance > 15.0) distance = 15.0; 
        
        d[id] = distance;
        received[id] = true;

        if (received[0] && received[1] && received[2]) {
           calculatePosition();
        }
      } 
    }
  }
}

void calculatePosition() {
  float d0_sq = sq(d[0]);
  float d1_sq = sq(d[1]);
  float d2_sq = sq(d[2]);
  
  // Calculate X (using Beacon 0 and 1)
  float dist_01 = sqrt(sq(bx[1] - bx[0]) + sq(by[1] - by[0])); 
  if (dist_01 == 0) dist_01 = 8.0; 
  float x = (d0_sq - d1_sq + sq(dist_01)) / (2 * dist_01);
  
  // Calculate Y (using Beacon 0 and 2)
  float dist_02 = sqrt(sq(bx[2] - bx[0]) + sq(by[2] - by[0])); 
  if (dist_02 == 0) dist_02 = 8.0; 
  float y = (d0_sq - d2_sq + sq(dist_02)) / (2 * dist_02);

  if (x < 0) x = 0; if (x > 8) x = 8;
  if (y < 0) y = 0; if (y > 8) y = 8;

  // Print Data
  Serial.println("\n-----------------------------");
  Serial.print("INPUTS: d0="); Serial.print(d[0]); 
  Serial.print("m | d1="); Serial.print(d[1]);
  Serial.print("m | d2="); Serial.print(d[2]); Serial.println("m");
  Serial.print("POS: X="); Serial.print(x, 1); Serial.print(" Y="); Serial.println(y, 1);
  
  // DRAW MAP
  drawMap(x, y);
  
  Serial.println("-----------------------------");
}

void drawMap(float userX, float userY) {
  // Map Size: 8x8 meters
  // Resolution: 1 character = 1 meter approx
  
  Serial.println("      (Y)");
  Serial.println("      ^");
  
  // Loop from Y=8 down to Y=0 (Top to Bottom)
  for (int y = 8; y >= 0; y--) {
    Serial.print("  ");
    Serial.print(y);
    Serial.print(" | ");
    
    // Loop from X=0 to X=8 (Left to Right)
    for (int x = 0; x <= 8; x++) {
      
      // Check if this grid point matches User Position (rounded)
      if (round(userX) == x && round(userY) == y) {
        Serial.print("X "); // The User
      } 
      // Check for Beacons
      else if (x==0 && y==0) Serial.print("0 "); // Beacon 0
      else if (x==8 && y==0) Serial.print("1 "); // Beacon 1
      else if (x==0 && y==8) Serial.print("2 "); // Beacon 2
      // Empty Space
      else {
        Serial.print(". ");
      }
    }
    Serial.println();
  }
  Serial.println("    -------------------");
  Serial.println("      0 1 2 3 4 5 6 7 8  (X)");
}