#include <MPU6050_tockn.h>
#include <Wire.h>

/*
 * Project: Wireless IoT FPS Controller (Latching)
 * Hardware: ESP32 WROOM, GY-521, Analog Joystick, Trigger Button
 * Feature: Input Latching to prevent missed clicks over WiFi/Serial
 */

MPU6050 mpu6050(Wire);

// ADC2 Pins (Joystick)
const int JOY_X_PIN = 4; 
const int JOY_Y_PIN = 2; 

// Button Pins
const int RECENTER_BTN_PIN = 5;   // Joystick Push (Recenter)
const int TRIGGER_BTN_PIN = 21;   // Trigger (Fire)

// TUNING
const int SAMPLES = 5;  
const int SAMPLE_DELAY = 1; 

// LATCHING CONFIG
// Keeps the button "Pressed" for a minimum cycles to ensure Python catches it
int triggerLatchCounter = 0; 
const int LATCH_FRAMES = 6; // Hold signal for ~60ms

void setup() {
  Serial.begin(115200);
  Wire.begin(23, 22); 
  
  mpu6050.begin();
  Serial.println(">>> CALIBRATING - KEEP STILL <<<");
  mpu6050.calcGyroOffsets(true);
  Serial.println(">>> CALIBRATION COMPLETE <<<");

  pinMode(RECENTER_BTN_PIN, INPUT_PULLUP);
  pinMode(TRIGGER_BTN_PIN, INPUT_PULLUP);
}

void loop() {
  float sumP = 0;
  float sumY = 0;

  // 1. OVERSAMPLING LOOP
  for (int i = 0; i < SAMPLES; i++) {
    mpu6050.update();
    sumP += mpu6050.getAngleX();
    sumY += mpu6050.getAngleZ();
    delay(SAMPLE_DELAY); 
  }

  // 2. AVERAGING
  float avgPitch = sumP / SAMPLES;
  float avgYaw = sumY / SAMPLES;

  // 3. READ INPUTS
  int joyX = analogRead(JOY_X_PIN);
  int joyY = analogRead(JOY_Y_PIN);
  
  // Read Physical Buttons
  int rawRecenter = digitalRead(RECENTER_BTN_PIN) == LOW ? 1 : 0;
  int rawFire = digitalRead(TRIGGER_BTN_PIN) == LOW ? 1 : 0;

  // 4. SIGNAL LATCHING (The Fix)
  // If we detect a press, reset the latch timer
  if (rawFire == 1) {
    triggerLatchCounter = LATCH_FRAMES;
  }

  // Determine Output State based on Latch
  int outputFire = 0;
  if (triggerLatchCounter > 0) {
    outputFire = 1;
    triggerLatchCounter--; // Countdown
  }

  // 5. SEND DATA
  Serial.print("P:");
  Serial.print(avgPitch, 2); 
  Serial.print(",Y:");
  Serial.print(avgYaw, 2);
  Serial.print(",JX:");
  Serial.print(joyX);
  Serial.print(",JY:");
  Serial.print(joyY);
  Serial.print(",C:");
  Serial.print(rawRecenter);
  Serial.print(",F:");
  Serial.print(outputFire); // Send the latched value
  Serial.println();
}