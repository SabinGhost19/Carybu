package com.sabinghost19.demowebsocket.model;


import com.sabinghost19.demowebsocket.enums.MessageType;
import lombok.*;
import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.mapping.Document;
import java.time.LocalDateTime;

@Getter
@Setter
@AllArgsConstructor
@NoArgsConstructor
@Builder
@Document(collection = "chat_messages")
public class ChatMessageEntity {
    @Id
    private String id;
    private MessageType type;
    private String content;
    private String sender;
    private LocalDateTime timestamp;
}