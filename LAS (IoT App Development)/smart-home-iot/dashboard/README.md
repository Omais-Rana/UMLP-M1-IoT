# Smart Home IoT Dashboard

A comprehensive IoT dashboard built with [Laravel](https://laravel.com) and [Filament](https://filamentphp.com) for monitoring and controlling smart home devices.

## Features

-   **Room Management**: Dedicated pages for Living Room, Dining Room, Bedroom, and Kitchen.
-   **Device Control**: Real-time control of devices (e.g., lights) using MQTT.
-   **Monitoring**: Dashboard widgets for room statistics and device status.
-   **Notifications**: Automated alerts for critical events (e.g., Door Open notifications).
-   **User Management**: Secure authentication and user management via Filament.

## Tech Stack

-   **Backend**: Laravel 12, PHP 8.2+
-   **Admin Panel**: Filament 4.2
-   **Frontend**: Blade, TailwindCSS, Vite
-   **IoT Protocols**: MQTT (via `php-mqtt/client`)
-   **Data Storage**: MySQL/SQLite (Application Data), InfluxDB (Sensor Data - _Dependency included_)

## Prerequisites

Ensure you have the following installed:

-   PHP >= 8.2
-   Composer
-   Node.js & NPM
-   An MQTT Broker (e.g., Mosquitto) running locally or accessible via network.

## Installation

1.  **Clone the repository**

    ```bash
    git clone <repository-url>
    cd dashboard
    ```

2.  **Install PHP dependencies**

    ```bash
    composer install
    ```

3.  **Install and build frontend assets**

    ```bash
    npm install
    npm run build
    ```

4.  **Environment Configuration**
    Copy the example environment file and configure your database settings.

    ```bash
    cp .env.example .env
    ```

    Update `.env` with your database credentials.

5.  **Generate Application Key**

    ```bash
    php artisan key:generate
    ```

6.  **Run Migrations**
    Set up the database tables.

    ```bash
    php artisan migrate
    ```

7.  **Seed Database (Optional)**
    Creates a default test user (`test@example.com`).
    ```bash
    php artisan db:seed
    ```

## Configuration

### MQTT Setup

Currently, MQTT settings are configured directly in the widget classes (e.g., `App\Filament\Widgets\RoomControls.php`).
Default configuration:

-   **Host**: `localhost`
-   **Port**: `1883`
-   **Client ID**: `laravel-sender`

Ensure your MQTT broker is running and accessible at these coordinates, or update the code to match your setup.

### Email Notifications

To enable email notifications (e.g., for door open alerts), you must configure the mail settings in your `.env` file.

Example configuration for SMTP (e.g., Mailtrap, Gmail, or a custom SMTP server):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Dashboard Customization

#### Grafana Panels

The Grafana panel URLs are currently hardcoded in `app/Filament/Pages/BaseRoomPage.php`.
Update the `getGrafanaUrl()` and `getHumidityUrl()` methods with your specific Grafana dashboard URL, UID, and Panel IDs.

#### Camera Feeds (Arducam)

Camera stream URLs are defined in each room's specific page class.
For example, to update the Living Room camera:

1. Open `app/Filament/Pages/LivingRoom.php`.
2. Update the `getCameraUrl()` method with your camera's IP address and stream path (e.g., `http://192.168.1.X:81/stream`).

## Usage

1.  **Start the development server**

    ```bash
    php artisan serve
    ```

2.  **Access the Dashboard**
    Open your browser and navigate to `http://localhost:8000/admin`.

3.  **Create a User**

    A default user is created if you seeded the database:

    -   **Email**: user@gmail.com
    -   **Password**: admin123

    If you didn't seed the database, create a new Filament user:

    ```bash
    php artisan make:filament-user
    ```

    Follow the prompts to set a name, email, and password.

## Project Structure

-   `app/Filament/Pages`: Contains room-specific dashboard pages.
-   `app/Filament/Widgets`: Contains dashboard widgets (Controls, Stats, Status).
-   `app/Models/HomeDevice`: Model representing IoT devices.
-   `app/Notifications`: System notifications (e.g., DoorOpenNotification).

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
