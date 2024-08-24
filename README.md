
# TweetAI

This project is a real-time Autobot counter using Laravel 11 and Vue.js. It displays the current count of Autobots created by a background process and updates in real-time.

## Setup Instructions

### Prerequisites

- PHP 8.2 or higher

- Composer

- Node.js and npm

- Laravel 11

- MySQL or another supported database

### Installation

1. Clone the repository:

```bash
git clone <git@github.com>:OfficialOzioma/tweetAI.git
```

2. Install PHP dependencies:

```bash  
composer install
```

3. Install JavaScript dependencies:

```bash
npm install
```

4. Copy the `.env.example` file to `.env` and configure your environment variables:

```bash
cp .env.example .env
```

5. Generate an application key:

```bash
php artisan key:generate
```

6. Set up your database in the `.env` file and run migrations:

```bash
php artisan migrate
```

7. Configure Pusher or your preferred WebSocket provider in the `.env` file.

### Running the Application

1. Start the Laravel development server:

```bash
php artisan serve
```

2. In a new terminal, compile and watch for asset changes:

```bash
npm run dev
```

3. Open your browser and navigate to `http://localhost:8000`.

## Background Process

The background process that creates Autobots should be set up as a separate Laravel command or job. Ensure it dispatches the `AutobotCreated` event after creating new Autobots.

## API Documentation

For detailed information about the API endpoints used in this project, please refer to our [API Documentation](https://api-docs.example.com/autobot-counter).

## Troubleshooting

- If real-time updates are not working, check your WebSocket configuration in the `.env` file and ensure the broadcast driver is set correctly.
- Make sure the `AutobotCreated` event is being dispatched correctly in your background process.
- Check the browser console for any JavaScript errors related to Laravel Echo or Vue.js.

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
