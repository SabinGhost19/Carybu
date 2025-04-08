import {
  Component,
  Input,
  OnDestroy,
  OnInit,
  ViewChild,
  ElementRef,
  AfterViewChecked,
} from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { WebSocketService } from '../../services/web-socket.service';
import { WebsocketConnectionService } from '../../services/websocket-connection.service';
import { ChatMessage, MessageType } from '../../models/chat-message.model';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [FormsModule, CommonModule],
  templateUrl: './chat.component.html',
  styleUrls: ['./chat.component.scss'],
})
export class ChatComponent implements OnInit, OnDestroy, AfterViewChecked {
  @Input() username: string = '';
  @ViewChild('messagesContainer') private messagesContainer!: ElementRef;

  MessageType = MessageType;
  messageContent: string = '';
  messages: ChatMessage[] = [];
  isConnected: boolean = false;
  isLoading: boolean = true;

  private colors = [
    '#2196F3',
    '#32c787',
    '#00BCD4',
    '#ff5652',
    '#ffc107',
    '#ff85af',
    '#FF9800',
    '#39bbb0',
  ];

  private messageSubscription: Subscription | null = null;
  private connectionSubscription: Subscription | null = null;

  constructor(
    private webSocketService: WebSocketService,
    private websocketConnection: WebsocketConnectionService
  ) {}

  ngOnInit(): void {
    this.messageSubscription = this.webSocketService.messages$.subscribe(
      (messages: ChatMessage[]) => {
        this.messages = messages;
        this.isLoading = false;
        setTimeout(() => this.scrollToBottom(), 100);
      }
    );

    this.connectionSubscription =
      this.websocketConnection.connectionStatus$.subscribe(
        (status: boolean) => {
          this.isConnected = status;
          if (status) {
            setTimeout(() => {
              this.isLoading = false;
            }, 1000);
          }
        }
      );

    setTimeout(() => {
      if (this.isLoading) {
        this.isLoading = false;
      }
    }, 8000);
  }

  ngAfterViewChecked() {
    this.scrollToBottom();
  }

  ngOnDestroy(): void {
    if (this.messageSubscription) {
      this.messageSubscription.unsubscribe();
    }
    if (this.connectionSubscription) {
      this.connectionSubscription.unsubscribe();
    }
    this.webSocketService.disconnect();
  }

  sendMessage(): void {
    if (this.messageContent.trim() && this.isConnected) {
      const message: ChatMessage = {
        content: this.messageContent.trim(),
        type: MessageType.CHAT,
        sender: this.username,
        time: new Date(),
      };
      this.webSocketService.sendMessage(message);
      this.messageContent = '';
    }
  }

  getMessage(message: ChatMessage): string {
    if (!message) return '';

    switch (message.type) {
      case MessageType.JOIN:
        return `${message.sender} joined the chat`;
      case MessageType.LEAVE:
        return `${message.sender} left the chat`;
      case MessageType.CHAT:
        return message.content || '';
      default:
        return '';
    }
  }

  getAvatarColor(sender: string): string {
    let hash = 0;
    for (let i = 0; i < sender.length; i++) {
      hash = 31 * hash + sender.charCodeAt(i);
    }
    const index = Math.abs(hash % this.colors.length);
    return this.colors[index];
  }

  getMessageClass(message: ChatMessage): string {
    if (!message) return '';

    if (message.type === MessageType.JOIN) {
      return 'join-message';
    } else if (message.type === MessageType.LEAVE) {
      return 'leave-message';
    } else if (message.type === MessageType.CHAT) {
      return message.sender === this.username
        ? 'sent-message'
        : 'received-message';
    }
    return '';
  }

  private scrollToBottom(): void {
    try {
      if (this.messagesContainer) {
        this.messagesContainer.nativeElement.scrollTop =
          this.messagesContainer.nativeElement.scrollHeight;
      }
    } catch (err) {
      console.error('Error scrolling to bottom:', err);
    }
  }
}
