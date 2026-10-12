# Patient Health Monitoring System

An IoT-based health monitoring prototype that reads a patient's **heart rate, blood oxygen (SpO2), body temperature and ambient temperature/humidity** using an **ESP32** and displays the readings in real time on the **Blynk** mobile app.

> Final Year Project (Diploma in Computer Science), College of Computing and Informatics, Universiti Tenaga Nasional (UNITEN), 2023/2024.
> **Author:** Zarul Fadzrul Ridzwan Bin Rizal  |  **Supervisor:** Mr. Eddren Law Yi Feng

> ⚠️ **Disclaimer:** This is an academic prototype built with low-cost hobbyist sensors. It is **not a medical device** and must not be used for diagnosis or treatment decisions. Always consult a qualified healthcare professional.

---

## Table of Contents
- [Background](#background)
- [Features](#features)
- [Hardware](#hardware)
- [Software and Libraries](#software-and-libraries)
- [Wiring](#wiring)
- [Getting Started](#getting-started)
- [Project Structure](#project-structure)
- [Web Dashboard](#web-dashboard)
- [How It Works](#how-it-works)
- [Requirements Survey Results](#requirements-survey-results)
- [Limitations and Future Work](#limitations-and-future-work)
- [Acknowledgements](#acknowledgements)
- [License](#license)

## Background
Keeping track of the health of chronic and elderly patients at home is difficult, especially for family members who are at work. This project proposes a low-cost, sensor-based system that lets patients and their caregivers monitor vital signs remotely, with the aim of reducing the number of hospital visits needed only for routine check-ups.

**Objectives**
1. Develop a system that monitors vital signs using heart rate and temperature sensors.
2. Make it easier for chronic and geriatric patients to keep monitoring their health.
3. Reduce the cost and time of hospital visits for routine check-ups.

**Target users:** individuals with long-term medical conditions, older adults, and patients who need ongoing assistance.

## Features
- Real-time heart rate (BPM) and blood oxygen saturation (SpO2) readings
- Body temperature measurement (DS18B20)
- Room temperature and humidity measurement (DHT11)
- Live dashboard on the Blynk mobile app over Wi-Fi
- Web dashboard (HTML + PHP) that shows the same readings in a browser and logs them
- Simple prototype wiring on a breadboard, no soldering required

## Hardware
| Component | Purpose |
|-----------|---------|
| ESP32 development board | Microcontroller with built-in Wi-Fi/Bluetooth |
| MAX30100 / MAX30102 | Heart rate and pulse oximetry sensor (I2C) |
| DHT11 | Ambient temperature and humidity |
| DS18B20 | Body temperature (needs a 4.7 kΩ pull-up resistor) |
| Breadboard + jumper wires (M-M / M-F) | Connections |
| USB cable | Power and programming |

> Note: the report refers to both MAX30100 and MAX30102. Keep only the one you actually used and update the library in `platformio.ini` accordingly.

## Software and Libraries
- Arduino IDE (or PlatformIO), C/C++
- [Blynk IoT](https://blynk.io) platform and mobile app
- Libraries: `Blynk`, `MAX30100lib` (or SparkFun MAX3010x), `DHT sensor library`, `Adafruit Unified Sensor`, `OneWire`, `DallasTemperature`
- ESP32 board package by Espressif

## Wiring
Pin numbers below are defaults defined in `include/config.h`. Change them to match your build.

| Sensor | Sensor Pin | ESP32 Pin |
|--------|-----------|-----------|
| MAX30100/2 | VIN | 3V3 |
| MAX30100/2 | GND | GND |
| MAX30100/2 | SDA | GPIO 21 |
| MAX30100/2 | SCL | GPIO 22 |
| DHT11 | VCC / GND | 3V3 / GND |
| DHT11 | DATA | GPIO 4 |
| DS18B20 | VCC / GND | 3V3 / GND |
| DS18B20 | DATA (+4.7 kΩ to 3V3) | GPIO 5 |

_Add your circuit diagram here:_ `![Circuit diagram](docs/images/circuit-diagram.png)`

## Getting Started

### 1. Set up Blynk
1. Create a free account at [blynk.cloud](https://blynk.cloud).
2. Create a **Template** (hardware: ESP32, connection: Wi-Fi).
3. Add datastreams (default mapping):

   | Virtual Pin | Data | Unit |
   |-------------|------|------|
   | V0 | Heart rate | BPM |
   | V1 | SpO2 | % |
   | V2 | Body temperature | °C |
   | V3 | Room temperature | °C |
   | V4 | Humidity | % |
4. Create a **Device** from the template and copy its Template ID, Template Name and Auth Token.

### 2. Configure credentials
```bash
cp include/secrets.example.h include/secrets.h
```
Edit `include/secrets.h` with your Wi-Fi and Blynk details. **Never commit this file**; it is listed in `.gitignore`.

### 3. Build and upload
**PlatformIO (recommended)**
```bash
pio run -t upload
pio device monitor
```
**Arduino IDE**
1. Install the ESP32 board package and the libraries listed above via Library Manager.
2. Open `src/main.cpp` as a sketch (rename to `.ino` if needed), keeping `include/` alongside it.
3. Select your ESP32 board and port, then upload.

### 4. Use it
Power the ESP32, wait for it to connect to Wi-Fi, then place a finger on the MAX30100/2 sensor. The red LED on the sensor indicates it is working. Readings appear in the Blynk app.

## Project Structure
```
.
├── src/
│   └── main.cpp              # Main firmware
├── include/
│   ├── config.h              # Pin mapping, intervals, virtual pins
│   └── secrets.example.h     # Template for Wi-Fi/Blynk credentials
├── web/
│   ├── health_monitoring_record.html  # Dashboard page
│   ├── health_record_data.php         # Backend: reads Blynk, logs data
│   └── config.example.php             # Template for the Blynk token
├── docs/
│   ├── FYP_Final_Report.pdf  # Full project report
│   └── images/               # Circuit diagram, app screenshots
├── platformio.ini
├── .gitignore
├── LICENSE
└── README.md
```

## Web Dashboard
Besides the Blynk app, the `web/` folder contains a simple browser dashboard.

```
ESP32 --> Blynk cloud --> health_record_data.php --> health_monitoring_record.html
                                  |
                                  +--> health_data.log
```

- `health_record_data.php` reads the latest values from the Blynk cloud, logs them (at most once every 30 s) and returns JSON. The Blynk token stays on the server and is never sent to the browser.
- `health_monitoring_record.html` calls the PHP file every 3 seconds and updates the page.

**Run it**
1. Install PHP 7.4+ with the cURL extension (or use XAMPP).
2. `cp web/config.example.php web/config.php` and enter your Blynk token and server region.
3. Start a local server:
   ```bash
   cd web
   php -S localhost:8000
   ```
4. Open `http://localhost:8000/health_monitoring_record.html`.

## How It Works
1. The ESP32 connects to Wi-Fi and to the Blynk cloud.
2. Sensors are sampled periodically (the pulse oximeter is polled continuously, other sensors every few seconds).
3. Readings are pushed to Blynk virtual pins and shown on the app dashboard.

**Development methodology:** Prototyping model (requirement gathering, quick design, prototype, user evaluation, refinement).

## Requirements Survey Results
A Google Forms questionnaire with 30 respondents was used for requirement gathering. Highlights:
- 100% agreed or strongly agreed that health monitoring is necessary.
- 100% agreed or strongly agreed they would use such a tool if available.
- Most respondents expected it to reduce hospital visit time and cost.

Full questionnaire and analysis are in the report (Appendix A and B).

## Limitations and Future Work
- Hobby-grade sensors (MAX30100/2, DHT11) are sensitive to finger placement and movement and are not clinically accurate.
- Possible improvements: alerts/notifications for abnormal readings, historical data logging and charts, a casing or wearable form factor, a more accurate sensor set, and caregiver multi-user access.

## Acknowledgements
Supervisor Mr. Eddren Law Yi Feng, panels Madam Saliza and Madam Nashrah, and family and friends at UNITEN.

## License
Released under the [MIT License](LICENSE).
