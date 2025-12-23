#include <MPU6050_tockn.h>
#include <Wire.h>

/*
 * Project: Wireless IoT FPS Controller (Wired Phase)
 * Hardware: ESP32 WROOM, GY-521, Analog Joystick
 * * Update: Moved Joystick to GPIO 4 and 2.
 * These pins are on the same side as 18/19 but support Analog Input (ADC2).
 * Note: GPIO 2 is also connected to the onboard LED.
 */

MPU6050 mpu6050(Wire);

// Updated Pins to ADC2 capable pins on the accessible side
const int JOY_X_PIN = 4; 
const int JOY_Y_PIN = 2; 
const int FIRE_BTN_PIN = 5;

void setup() {
  Serial.begin(115200);
  
  // Initialize I2C (SDA=23, SCL=22)
  Wire.begin(23, 22);
  
  mpu6050.begin();
  Serial.println(">>> CALIBRATING IMU - KEEP STILL <<<");
  mpu6050.calcGyroOffsets(true);
  Serial.println(">>> CALIBRATION COMPLETE <<<");

  pinMode(FIRE_BTN_PIN, INPUT_PULLUP);
}

void loop() {
  mpu6050.update();

  // Read Joystick Values (Now using ADC2 pins)
  int joyX = analogRead(JOY_X_PIN);
  int joyY = analogRead(JOY_Y_PIN);
  
  // Read Button (Active Low)
  int btnState = digitalRead(FIRE_BTN_PIN) == LOW ? 1 : 0;

  // Get stable angles from MPU-6050
  float pitch = mpu6050.getAngleX();
  float yaw   = mpu6050.getAngleZ();

  // Output string for the Bridge
  // Format: P:[pitch],Y:[yaw],JX:[joyX],JY:[joyY],B:[btn]
  Serial.print("P:");
  Serial.print(pitch);
  Serial.print(",Y:");
  Serial.print(yaw);
  Serial.print(",JX:");
  Serial.print(joyX);
  Serial.print(",JY:");
  Serial.print(joyY);
  Serial.print(",B:");
  Serial.print(btnState);
  Serial.println();

  delay(10); // 100Hz refresh rate
}