import { Component, EventEmitter, Output } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [FormsModule, CommonModule],
  template: `
    <div id="username-page">
      <div class="username-page-container">
        <h1 class="title">Type your username to enter the Chatroom</h1>
        <form (ngSubmit)="onSubmit()">
          <div class="form-group">
            <input
              type="text"
              [(ngModel)]="username"
              name="username"
              placeholder="Username"
              autocomplete="off"
              class="form-control"
              required
            />
          </div>
          <div class="form-group">
            <button
              type="submit"
              class="accent username-submit"
              [disabled]="!username.trim()"
            >
              Start Chatting
            </button>
          </div>
        </form>
      </div>
    </div>
  `,
  styles: [
    `
      #username-page {
        text-align: center;
        background-color: #f8f9fa;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .username-page-container {
        background-color: white;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        padding: 30px;
        width: 100%;
        max-width: 500px;
      }

      .form-group {
        margin-bottom: 15px;
      }

      .form-control {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
      }

      .accent {
        background-color: #2196f3;
        border: none;
        color: white;
        padding: 12px 20px;
        cursor: pointer;
        border-radius: 4px;
        font-size: 16px;
        width: 100%;
      }

      .accent:hover {
        background-color: #0b7dda;
      }

      .accent:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
      }
    `,
  ],
})
export class LoginComponent {
  username: string = '';

  @Output() userLogin = new EventEmitter<string>();

  onSubmit(): void {
    if (this.username.trim()) {
      this.userLogin.emit(this.username.trim());
    }
  }
}
