package com.sabinghost19.demowebsocket.service;

import com.sabinghost19.demowebsocket.DTOs.ChatMessageDTO;
import com.sabinghost19.demowebsocket.model.ChatMessage;
import com.sabinghost19.demowebsocket.model.ChatMessageEntity;
import com.sabinghost19.demowebsocket.repository.ChatMessageRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import java.time.LocalDateTime;
import java.util.List;

@Service
public class ChatMessageService {

    private final ChatMessageRepository chatMessageRepository;

    @Autowired
    public ChatMessageService(ChatMessageRepository chatMessageRepository) {
        this.chatMessageRepository = chatMessageRepository;
    }


    public void saveMessageToDatabase(ChatMessage chatMessage) {
        ChatMessageEntity chatMessageEntity = ChatMessageEntity.builder()
                .type(chatMessage.getType())
                .sender(chatMessage.getSender())
                .content(chatMessage.getContent())
                .timestamp(LocalDateTime.now())
                .build();
        this.chatMessageRepository.save(chatMessageEntity);
    }

    public List<ChatMessageDTO> getMessages(){
        return chatMessageRepository.findByOrderByTimestampAsc()
                .stream()
                .map(this::convertToDto)
                .toList();
    }

    private ChatMessageDTO convertToDto(ChatMessageEntity entity) {
        return ChatMessageDTO.builder()
                .type(entity.getType())
                .content(entity.getContent())
                .sender(entity.getSender())
                .timestamp(entity.getTimestamp())
                .build();
    }

}

