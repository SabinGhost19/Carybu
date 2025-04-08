# Angular WebSocket Chat Application

This is an Angular implementation of a chat interface using WebSockets with SockJS and STOMP.js. It mimics the functionality of the Spring Boot WebSocket Chat Application example.

## Features

- User login screen
- Real-time chat messaging
- Join/Leave notifications
- User avatars with unique colors based on username
- Responsive design

## Prerequisites

- Node.js and npm
- Angular CLI
- A WebSocket backend server running on http://localhost:8080 (configured in proxy.conf.json)

## Installation

1. Clone the repository
2. Navigate to the project directory
3. Install dependencies:

```bash
npm install
```

## Running the Application

Start the development server:

```bash
npm start
```

The application will be available at http://localhost:4200

## Backend Server

This chat interface expects a WebSocket backend server with the following endpoints:

- `/ws` - WebSocket connection endpoint
- `/app/chat.addUser` - Endpoint to notify when a user joins the chat
- `/app/chat.sendMessage` - Endpoint to send messages to all users
- `/topic/public` - Topic to subscribe to for receiving messages

The backend should handle the following message types:

- JOIN - When a user joins the chat
- LEAVE - When a user leaves the chat
- CHAT - When a user sends a message

## Development

This project was generated with Angular CLI. You can use standard Angular CLI commands for development:

- `ng generate component component-name` - Generate a new component
- `ng build` - Build the project
- `ng test` - Execute unit tests
- `ng e2e` - Execute end-to-end tests

## License

This project is open source and available under the MIT License.
