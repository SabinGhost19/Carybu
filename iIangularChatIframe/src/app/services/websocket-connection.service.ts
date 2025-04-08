import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { ChatMessage, MessageType } from '../models/chat-message.model';

@Injectable({
  providedIn: 'root',
})
export class WebsocketConnectionService {
  private stompClient: any;
  private connectionStatus = new BehaviorSubject<boolean>(false);
  private reconnectAttempts = 0;
  private maxReconnectAttempts = 5;
  private reconnectTimeout: any = null;
  private serverUrl = 'http://localhost:8080';

  connectionStatus$ = this.connectionStatus.asObservable();

  constructor() {
    this.loadExternalScripts();
  }

  private loadExternalScripts() {
    const sockjsScript = document.createElement('script');
    sockjsScript.src =
      'https://cdn.jsdelivr.net/npm/sockjs-client@1.5.0/dist/sockjs.min.js';
    sockjsScript.async = true;

    const stompScript = document.createElement('script');
    stompScript.src =
      'https://cdn.jsdelivr.net/npm/stompjs@2.3.3/lib/stomp.min.js';
    stompScript.async = true;

    document.body.appendChild(sockjsScript);
    document.body.appendChild(stompScript);
  }

  connect(
    username: string,
    onConnect: () => void,
    onMessage: (message: ChatMessage) => void,
    onError: (error: any) => void
  ): void {
    if (
      typeof (window as any).SockJS === 'undefined' ||
      typeof (window as any).Stomp === 'undefined'
    ) {
      setTimeout(
        () => this.connect(username, onConnect, onMessage, onError),
        500
      );
      return;
    }

    try {
      const SockJS = (window as any).SockJS;
      const Stomp = (window as any).Stomp;

      const socket = new SockJS(`${this.serverUrl}/ws`, null, {
        transports: ['websocket', 'xhr-streaming', 'xhr-polling'],
        timeout: 10000,
      });

      this.stompClient = Stomp.over(socket);
      this.stompClient.debug = null;

      this.stompClient.connect(
        {},
        () => {
          console.log('connected to websocket');
          this.connectionStatus.next(true);
          this.reconnectAttempts = 0;

          this.stompClient.subscribe('/topic/public', (payload: any) => {
            const message: ChatMessage = JSON.parse(payload.body);
            onMessage(message);
          });

          onConnect();
        },
        (error: any) => {
          console.error('websocket connection failed:', error);
          this.connectionStatus.next(false);
          onError(error);

          if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            this.reconnectTimeout = setTimeout(() => {
              this.connect(username, onConnect, onMessage, onError);
            }, 3000);
          }
        }
      );
    } catch (err) {
      console.error('websocket initialization error:', err);
      this.connectionStatus.next(false);
      onError(err);
    }
  }

  disconnect(): void {
    if (this.reconnectTimeout) {
      clearTimeout(this.reconnectTimeout);
    }

    if (this.stompClient) {
      this.stompClient.disconnect();
      this.connectionStatus.next(false);
    }
  }

  sendMessage(message: ChatMessage): void {
    if (this.stompClient && this.connectionStatus.value) {
      if (!message.time) {
        message.time = new Date();
      }

      this.stompClient.send(
        '/app/chat.sendMessage',
        {},
        JSON.stringify(message)
      );
    }
  }

  isConnected(): boolean {
    return this.connectionStatus.value;
  }
}
