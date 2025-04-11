import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject } from 'rxjs';
import { ChatMessage, MessageType } from '../models/chat-message.model';

@Injectable({
  providedIn: 'root',
})
export class MessageHistoryService {
  private messageSubject = new BehaviorSubject<ChatMessage[]>([]);
  private serverUrl = 'http://localhost:8080';

  messages$ = this.messageSubject.asObservable();

  constructor(private http: HttpClient) {}

  fetchMessageHistory(): Promise<void> {
    const httpOptions = {
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      withCredentials: true,
    };

    const timeoutPromise = new Promise<void>((resolve) => {
      setTimeout(() => {
        console.log('history fetch timed out');
        resolve();
      }, 5000);
    });

    const fetchPromise = new Promise<void>((resolve, reject) => {
      this.http
        .get<any[]>(`${this.serverUrl}/chat/history`, httpOptions)
        .subscribe({
          next: (history) => {
            if (!history || history.length === 0) {
              resolve();
              return;
            }

            const chatMessages: ChatMessage[] = history.map((msg) => {
              const type = this.convertMessageType(msg.type);
              const timestamp =
                msg.timestamp || msg.time || new Date().toISOString();

              return {
                sender: msg.sender,
                content: msg.content,
                type: type,
                time: new Date(timestamp),
              };
            });

            this.messageSubject.next(chatMessages);
            resolve();
          },
          error: (error) => {
            reject(error);
          },
        });
    });

    return Promise.race([fetchPromise, timeoutPromise]);
  }

  private convertMessageType(type: any): MessageType {
    if (!type) return MessageType.CHAT;

    if (typeof type === 'string') {
      const upperType = type.toUpperCase();

      if (upperType === 'JOIN') return MessageType.JOIN;
      if (upperType === 'LEAVE') return MessageType.LEAVE;
      if (upperType === 'CHAT') return MessageType.CHAT;
    }

    return MessageType.CHAT;
  }

  addMessage(message: ChatMessage): void {
    const currentMessages = this.messageSubject.value;
    this.messageSubject.next([...currentMessages, message]);
  }

  getMessages(): ChatMessage[] {
    return this.messageSubject.value;
  }
}
