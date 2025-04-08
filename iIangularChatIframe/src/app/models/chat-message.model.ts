export enum MessageType {
  CHAT = 'CHAT',
  JOIN = 'JOIN',
  LEAVE = 'LEAVE',
}

export interface ChatMessage {
  sender: string;
  content?: string;
  type: MessageType;
  time: Date;
}
