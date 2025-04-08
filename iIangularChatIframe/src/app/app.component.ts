import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { WebSocketService } from './services/web-socket.service';
import { ChatMessage, MessageType } from './models/chat-message.model';
import { Subscription } from 'rxjs';
import { MessageHistoryService } from './services/message-history.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <div class="chat-container">
      <div class="chat-header">
        <h1>Chat Room</h1>
        <div class="user-info" *ngIf="username">
          <span class="user-badge">{{ username }}</span>
          <div class="action-buttons">
            <button (click)="loadHistory()" class="action-btn history-btn">
              <i class="fas fa-history"></i> History
            </button>
            <button (click)="disconnect()" class="action-btn disconnect-btn">
              <i class="fas fa-sign-out-alt"></i> Disconnect
            </button>
          </div>
        </div>
        <div class="login-form" *ngIf="!username">
          <input
            [(ngModel)]="loginUsername"
            placeholder="Enter your username"
            class="login-input"
          />
          <button
            (click)="connect()"
            [disabled]="!loginUsername"
            class="connect-btn"
          >
            <i class="fas fa-sign-in-alt"></i> Connect
          </button>
        </div>
      </div>

      <div class="chat-messages" #messageContainer>
        <div
          *ngFor="let message of messages"
          [ngClass]="{
            message: true,
            'my-message': message.sender === username,
            'system-message': message.type !== MessageType.CHAT
          }"
        >
          <div class="message-header">
            <span class="sender">{{ message.sender }}</span>
            <span class="time">{{ message.time | date : 'HH:mm' }}</span>
          </div>
          <div class="message-content">
            <ng-container [ngSwitch]="message.type">
              <span *ngSwitchCase="MessageType.JOIN" class="system-text">
                {{ message.sender }} joined the conversation
              </span>
              <span *ngSwitchCase="MessageType.LEAVE" class="system-text">
                {{ message.sender }} left the conversation
              </span>
              <span *ngSwitchDefault>{{ message.content }}</span>
            </ng-container>
            <div class="message-timestamp">
              {{ message.time | date : 'dd MMM yyyy' }}
            </div>
          </div>
        </div>
      </div>

      <div class="chat-input" *ngIf="username">
        <input
          [(ngModel)]="newMessage"
          (keyup.enter)="sendMessage()"
          placeholder="Type a message..."
          class="message-input"
        />
        <button
          (click)="sendMessage()"
          [disabled]="!newMessage"
          class="send-btn"
        >
          <i class="fas fa-paper-plane"></i> Send
        </button>
      </div>
    </div>
  `,
  styles: [
    `
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
      @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

      .chat-container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        background: #ffffff;
        font-family: 'Poppins', sans-serif;
      }

      .chat-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eaeaea;
      }

      h1 {
        font-size: 24px;
        font-weight: 600;
        color: #333;
        margin: 0 0 15px 0;
      }

      .user-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 10px;
      }

      .user-badge {
        background: #f0f7ff;
        color: #2196f3;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
      }

      .action-buttons {
        display: flex;
        gap: 8px;
      }

      .login-form {
        display: flex;
        gap: 10px;
        margin-top: 10px;
      }

      .chat-messages {
        height: 400px;
        overflow-y: auto;
        padding: 15px;
        border: 1px solid #eaeaea;
        border-radius: 8px;
        margin-bottom: 20px;
        background: #f9f9f9;
      }

      .message {
        margin-bottom: 12px;
        padding: 12px;
        border-radius: 8px;
        background: #f5f5f5;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        max-width: 80%;
      }

      .my-message {
        background: #e3f2fd;
        margin-left: auto;
      }

      .system-message {
        background: #fff3e0;
        text-align: center;
        font-style: italic;
        max-width: 90%;
        margin: 12px auto;
      }

      .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
      }

      .sender {
        font-weight: 500;
        font-size: 0.9em;
        color: #2196f3;
      }

      .time {
        font-size: 0.85em;
        color: #888;
        font-weight: 500;
        background: rgba(0, 0, 0, 0.05);
        padding: 2px 6px;
        border-radius: 4px;
      }

      .message-content {
        word-break: break-word;
        line-height: 1.4;
      }

      .message-timestamp {
        font-size: 0.75em;
        color: #999;
        margin-top: 4px;
        text-align: right;
      }

      .my-message .message-timestamp {
        text-align: right;
      }

      .system-message .message-timestamp {
        text-align: center;
      }

      .chat-input {
        display: flex;
        gap: 10px;
      }

      input {
        flex: 1;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        transition: border-color 0.3s;
      }

      input:focus {
        outline: none;
        border-color: #2196f3;
      }

      button {
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s;
      }

      button i {
        font-size: 14px;
      }

      button:disabled {
        background: #e0e0e0;
        color: #9e9e9e;
        cursor: not-allowed;
      }

      .connect-btn {
        background: #2196f3;
        color: white;
      }

      .connect-btn:hover:not(:disabled) {
        background: #1976d2;
      }

      .disconnect-btn {
        background: #f44336;
        color: white;
      }

      .disconnect-btn:hover {
        background: #d32f2f;
      }

      .history-btn {
        background: #4caf50;
        color: white;
      }

      .history-btn:hover {
        background: #388e3c;
      }

      .send-btn {
        background: #2196f3;
        color: white;
      }

      .send-btn:hover:not(:disabled) {
        background: #1976d2;
      }

      .system-text {
        color: #666;
      }

      /* Custom scrollbar */
      .chat-messages::-webkit-scrollbar {
        width: 6px;
      }

      .chat-messages::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
      }

      .chat-messages::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
      }

      .chat-messages::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
      }
    `,
  ],
})
export class AppComponent implements OnInit, OnDestroy {
  username: string = '';
  loginUsername: string = '';
  messages: ChatMessage[] = [];
  newMessage: string = '';
  private messageSubscription?: Subscription;
  private connectionSubscription?: Subscription;
  MessageType = MessageType;

  constructor(
    private webSocketService: WebSocketService,
    private messageHistoryService: MessageHistoryService
  ) {}

  ngOnInit() {
    this.messageSubscription = this.messageHistoryService.messages$.subscribe(
      (messages: ChatMessage[]) => {
        this.messages = messages;
        this.scrollToBottom();
      }
    );

    this.connectionSubscription =
      this.webSocketService.connectionStatus$.subscribe(
        (isConnected: boolean) => {
          if (!isConnected) {
            this.disconnect();
          }
        }
      );

    // Fetch message history when component is mounted
    console.log('Component mounted, fetching message history...');
    this.fetchInitialHistory();
  }

  ngOnDestroy() {
    this.messageSubscription?.unsubscribe();
    this.connectionSubscription?.unsubscribe();
    this.disconnect();
  }

  connect() {
    if (this.loginUsername.trim()) {
      this.username = this.loginUsername.trim();
      this.webSocketService.connect(this.username);
    }
  }

  disconnect() {
    this.webSocketService.disconnect();
    this.username = '';
    this.loginUsername = '';
    this.messages = [];
  }

  sendMessage() {
    if (this.newMessage.trim()) {
      this.webSocketService.sendMessage({
        sender: this.username,
        content: this.newMessage.trim(),
        type: MessageType.CHAT,
        time: new Date(),
      });
      this.newMessage = '';
    }
  }

  private scrollToBottom() {
    const container = document.querySelector('.chat-messages');
    if (container) {
      container.scrollTop = container.scrollHeight;
    }
  }

  loadHistory() {
    console.log('Manually loading history');
    // Add a loading indicator
    const loadingMessage: ChatMessage = {
      sender: 'System',
      content: 'Loading message history...',
      type: MessageType.CHAT,
      time: new Date(),
    };

    // Add the loading message to the UI
    this.messages = [loadingMessage];

    // Fetch history
    this.messageHistoryService
      .fetchMessageHistory()
      .then(() => {
        console.log('History loaded successfully');
      })
      .catch((error: Error) => {
        console.error('Failed to load history:', error);
        // Add an error message
        const errorMessage: ChatMessage = {
          sender: 'System',
          content: 'Failed to load message history. Please try again.',
          type: MessageType.CHAT,
          time: new Date(),
        };
        this.messages = [errorMessage];
      });
  }

  // New method to fetch initial history
  private fetchInitialHistory(): void {
    // Add a loading indicator
    const loadingMessage: ChatMessage = {
      sender: 'System',
      content: 'Loading message history...',
      type: MessageType.CHAT,
      time: new Date(),
    };

    // Add the loading message to the UI
    this.messages = [loadingMessage];

    // Fetch history
    this.messageHistoryService
      .fetchMessageHistory()
      .then(() => {
        console.log('Initial history loaded successfully');
      })
      .catch((error: Error) => {
        console.error('Failed to load initial history:', error);
        // Add an error message
        const errorMessage: ChatMessage = {
          sender: 'System',
          content: 'Failed to load message history. Please try again.',
          type: MessageType.CHAT,
          time: new Date(),
        };
        this.messages = [errorMessage];
      });
  }
}
