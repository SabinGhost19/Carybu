package com.sabinghost19.demowebsocket.DTOs;


import com.sabinghost19.demowebsocket.enums.MessageType;
import lombok.*;

import java.time.LocalDateTime;

@Getter
@Setter
@AllArgsConstructor
@NoArgsConstructor
@Builder
public class ChatMessageDTO {
    private MessageType type;
    private String content;
    private String sender;
    private LocalDateTime timestamp;
}