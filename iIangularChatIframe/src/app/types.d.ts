declare module 'sockjs-client' {
  export default class SockJS {
    constructor(url: string, _reserved?: any, options?: any);
    onopen: () => void;
    onmessage: (event: any) => void;
    onclose: () => void;
    onerror: (error: any) => void;
    send(data: string): void;
    close(): void;
  }
}

declare module 'stompjs' {
  export function over(ws: any): any;
}
