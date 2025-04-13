package com.sabinghost19.demowebsocket.controller;

import com.sabinghost19.demowebsocket.DTOs.ChatMessageDTO;
import com.sabinghost19.demowebsocket.service.ChatMessageService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.CrossOrigin;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

@RestController
@CrossOrigin(originPatterns = "*", allowCredentials = "true")
public class ChatHistoryController {

    private final ChatMessageService chatMessageService;

    @Autowired
    public ChatHistoryController(ChatMessageService chatMessageService) {
        this.chatMessageService = chatMessageService;
    }

    @GetMapping("/chat/history")
    public ResponseEntity<List<ChatMessageDTO>>getMessages(){
        System.out.println("getMessages");
        return ResponseEntity.ok().body(this.chatMessageService.getMessages());
    }
}
