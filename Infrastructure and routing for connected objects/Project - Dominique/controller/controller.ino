#include <MPU6050_tockn.h>
#include <Wire.h>

/*
 * Project: Wireless IoT FPS Controller (Audio + Latching)
 * Hardware: ESP32 WROOM, GY-521, Analog Joystick, Trigger Button, Passive Buzzer
 */

MPU6050 mpu6050(Wire);

// ADC2 Pins (Joystick)
const int JOY_X_PIN = 4; 
const int JOY_Y_PIN = 2; 

// Button Pins
const int RECENTER_BTN_PIN = 5;   // Joystick Push (Recenter)
const int TRIGGER_BTN_PIN = 21;   // Trigger (Fire)
const int BUZZER_PIN = 18;        // Passive Buzzer (+ Leg)

// TUNING
const int SAMPLES = 5;  
const int SAMPLE_DELAY = 1; 

// LATCHING CONFIG
int triggerLatchCounter = 0; 
const int LATCH_FRAMES = 6; 

// AUDIO STATE MANAGEMENT
bool isPlayingSound = false;
bool isWaitingForAudio = false;   // Waiting for the delay timer
unsigned long triggerPressTime = 0;
unsigned long soundStartTime = 0;
int lastTriggerState = 0; 

// LATENCY COMPENSATION
// Increase this if sound plays before the gun fires on screen
// Decrease this if sound plays too late
const int AUDIO_SYNC_DELAY = 150; // ms

void setup() {
  Serial.begin(115200);
  Wire.begin(23, 22); 
  
  mpu6050.begin();
  Serial.println(">>> CALIBRATING - KEEP STILL <<<");
  mpu6050.calcGyroOffsets(true);
  Serial.println(">>> CALIBRATION COMPLETE <<<");

  pinMode(RECENTER_BTN_PIN, INPUT_PULLUP);
  pinMode(TRIGGER_BTN_PIN, INPUT_PULLUP);
  
  // Setup Buzzer
  pinMode(BUZZER_PIN, OUTPUT);
  // Optional: Chirp on startup
  tone(BUZZER_PIN, 1000, 100); 
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
  
  int rawRecenter = digitalRead(RECENTER_BTN_PIN) == LOW ? 1 : 0;
  int rawFire = digitalRead(TRIGGER_BTN_PIN) == LOW ? 1 : 0;

  // 4. AUDIO LOGIC (Delayed "Pew")
  
  // Detect Initial Press
  if (rawFire == 1 && lastTriggerState == 0) {
    isWaitingForAudio = true;
    triggerPressTime = millis();
  }
  lastTriggerState = rawFire;

  // Check if delay has passed
  if (isWaitingForAudio && (millis() - triggerPressTime >= AUDIO_SYNC_DELAY)) {
    isWaitingForAudio = false;
    isPlayingSound = true;
    soundStartTime = millis();
  }

  // Play Sound Effect
  if (isPlayingSound) {
    unsigned long elapsed = millis() - soundStartTime;
    if (elapsed < 150) {
      // Frequency Sweep: Drop from 2000Hz to 500Hz
      int freq = map(elapsed, 0, 150, 2000, 500);
      tone(BUZZER_PIN, freq);
    } else {
      noTone(BUZZER_PIN); // Stop sound
      isPlayingSound = false;
    }
  }

  // 5. SIGNAL LATCHING (For Python/Laravel)
  // We still send the signal immediately so the server starts processing
  if (rawFire == 1) {
    triggerLatchCounter = LATCH_FRAMES;
  }

  int outputFire = 0;
  if (triggerLatchCounter > 0) {
    outputFire = 1;
    triggerLatchCounter--; 
  }

  // 6. SEND DATA
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
  Serial.print(outputFire); 
  Serial.println();
}