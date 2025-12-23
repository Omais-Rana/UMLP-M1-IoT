import serial
import requests
import time

# --- Configuration ---
SERIAL_PORT = 'COM5' 
BAUD_RATE = 115200
# Ensure this matches your Herd URL
LARAVEL_URL = 'http://game.test/api/controller-data' 

def main():
    try:
        # Reduced timeout for faster non-blocking reads
        ser = serial.Serial(SERIAL_PORT, BAUD_RATE, timeout=0.05)
        print(f"Connected to ESP32 on {SERIAL_PORT}")
        print("Bridge Running... (Buffer clearing enabled)")
        
        while True:
            # 1. OPTIMIZATION: Check if data is waiting
            if ser.in_waiting > 0:
                # 2. Read the ENTIRE buffer at once, clearing the backlog
                try:
                    raw_block = ser.read(ser.in_waiting).decode('utf-8', errors='ignore')
                    lines = raw_block.split('\n')
                    
                    # We need at least 2 lines to ensure we have one full complete line
                    # lines[-1] is usually empty or partial. lines[-2] is the last full packet.
                    if len(lines) >= 2:
                        latest_line = lines[-2].strip()
                        
                        if not latest_line:
                            continue

                        # Debug: See exactly what we are sending
                        # print(f"Sending: {latest_line}") 

                        # 3. Parse and Send
                        parts = latest_line.split(',')
                        data = {}
                        for part in parts:
                            if ':' in part:
                                key, val = part.split(':')
                                data[key] = float(val) if '.' in val else int(val)
                        
                        # 4. Send to Laravel (Timeout prevents hanging)
                        requests.post(LARAVEL_URL, json=data, timeout=0.2)
                
                except Exception as e:
                    # Ignore occasional parsing/network errors to keep stream alive
                    pass
            
            # small sleep to prevent CPU spiking
            time.sleep(0.01)

    except serial.SerialException as e:
        print(f"Serial Error: {e}")
        print("Check if Arduino IDE Serial Monitor is open!")
    except KeyboardInterrupt:
        print("Bridge stopped.")

if __name__ == "__main__":
    main()