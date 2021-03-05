module.exports = {
    apps: [
        {
            name: "Queue Worker",
            script: "artisan",
            interpreter: "php",
            exec_mode: "fork",
            args: "queue:work",
        },
        {
            name: "Mailhog Server",
            script: "mailhog",
            exec_mode: "fork",
        },
        {
            name: "Backend Server",
            script: "artisan",
            interpreter: "php",
            exec_mode: "fork",
            args: "serve",
        },
        {
            name: "Backend Schedule Runner",
            script: "artisan",
            interpreter: "php",
            exec_mode: "fork",
            args: "schedule:run",
        },
    ],
};
