import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { ChatMessage, MessageType } from '../models/chat-message.model';
import { MessageHistoryService } from './message-history.service';
import { WebsocketConnectionService } from './websocket-connection.service';

// don't use direct imports for sockjs and stomp
// we'll access them through the global window object

@Injectable({
  providedIn: 'root',
})
export class WebSocketService {
  private username: string = '';
  private connectionTimeout: any = null;
  private readonly CONNECTION_TIMEOUT = 10000; // 10 seconds

  messages$: any;

  constructor(
    private messageHistoryService: MessageHistoryService,
    private websocketConnection: WebsocketConnectionService
  ) {
    this.messages$ = this.messageHistoryService.messages$;
  }

  connect(username: string): void {
    this.username = username;
    this.setupConnectionTimeout();

    this.websocketConnection.connect(
      username,
      () => {
        this.clearConnectionTimeout();
        this.messageHistoryService.fetchMessageHistory();
      },
      (message: ChatMessage) => {
        this.messageHistoryService.addMessage(message);
      },
      (error: any) => {
        this.clearConnectionTimeout();
        console.error('WebSocket connection error:', error);
      }
    );
  }

  disconnect(): void {
    this.clearConnectionTimeout();
    this.websocketConnection.disconnect();
  }

  sendMessage(message: ChatMessage): void {
    if (this.websocketConnection.isConnected()) {
      this.websocketConnection.sendMessage(message);
    } else {
      console.error('Cannot send message: WebSocket is not connected');
    }
  }

  private setupConnectionTimeout(): void {
    this.clearConnectionTimeout();
    this.connectionTimeout = setTimeout(() => {
      if (!this.websocketConnection.isConnected()) {
        console.error('WebSocket connection timeout');
        this.disconnect();
      }
    }, this.CONNECTION_TIMEOUT);
  }

  private clearConnectionTimeout(): void {
    if (this.connectionTimeout) {
      clearTimeout(this.connectionTimeout);
      this.connectionTimeout = null;
    }
  }

  get connectionStatus$() {
    return this.websocketConnection.connectionStatus$;
  }
}
