package com.sabinghost19.demowebsocket.repository;

import com.sabinghost19.demowebsocket.model.ChatMessageEntity;
import org.springframework.data.mongodb.repository.MongoRepository;

import java.util.List;


public interface ChatMessageRepository extends MongoRepository<ChatMessageEntity, String> {

    List<ChatMessageEntity>findByOrderByTimestampAsc();
}