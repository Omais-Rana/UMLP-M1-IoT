#include <MPU6050_tockn.h>
#include <Wire.h>

/*
 * Project: Wireless IoT FPS Controller (Oversampled)
 * Hardware: ESP32 WROOM, GY-521, Analog Joystick, Trigger Button
 * Improvements: 
 * - Joystick Button (Pin 5) now RECENTERS the view (Key: 'C')
 * - Trigger Button (Pin 21) FIRES (Key: 'F')
 */

MPU6050 mpu6050(Wire);

// ADC2 Pins (Note: Use ADC1 pins 32-39 if you add WiFi later)
const int JOY_X_PIN = 4; 
const int JOY_Y_PIN = 2; 

// Button Pins
const int RECENTER_BTN_PIN = 5; // Joystick Push Button (Recenter)
const int TRIGGER_BTN_PIN = 21; // New Trigger Button (Fire)

// --- TUNING ---
const int SAMPLES = 8;        // Higher = Smoother, Lower = Faster
const int SAMPLE_DELAY = 1;   // Delay between samples in ms

void setup() {
  Serial.begin(115200);
  Wire.begin(23, 22); // SDA, SCL
  
  mpu6050.begin();
  // Ensure the gyro is stationary during this phase
  Serial.println(">>> CALIBRATING IMU - KEEP STILL <<<");
  mpu6050.calcGyroOffsets(true);
  Serial.println(">>> CALIBRATION COMPLETE <<<");

  // Use Internal Pullups (Connect buttons to GND)
  pinMode(RECENTER_BTN_PIN, INPUT_PULLUP);
  pinMode(TRIGGER_BTN_PIN, INPUT_PULLUP);
}

void loop() {
  float sumP = 0;
  float sumY = 0;

  // 1. OVERSAMPLING LOOP
  for(int i = 0; i < SAMPLES; i++) {
    mpu6050.update();
    sumP += mpu6050.getAngleX();
    sumY += mpu6050.getAngleZ();
    delay(SAMPLE_DELAY); 
  }

  // Calculate Average
  float pitch = sumP / SAMPLES;
  float yaw = sumY / SAMPLES;

  // 2. READ JOYSTICK
  int joyX = analogRead(JOY_X_PIN);
  int joyY = analogRead(JOY_Y_PIN);
  
  // 3. READ BUTTONS
  // Logic: LOW means pressed (due to Pullup)
  int btnRecenter = digitalRead(RECENTER_BTN_PIN) == LOW ? 1 : 0;
  int btnTrigger = digitalRead(TRIGGER_BTN_PIN) == LOW ? 1 : 0;

  // 4. OUTPUT
  // Format: P:_,Y:_,JX:_,JY:_,C:_,F:_
  Serial.print("P:");
  Serial.print(pitch, 2);
  Serial.print(",Y:");
  Serial.print(yaw, 2);
  Serial.print(",JX:");
  Serial.print(joyX);
  Serial.print(",JY:");
  Serial.print(joyY);
  Serial.print(",C:"); // Recenter Key
  Serial.print(btnRecenter);
  Serial.print(",F:"); // Fire Key
  Serial.print(btnTrigger);
  Serial.println();
}