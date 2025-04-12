import { Component, EventEmitter, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { SentimentService } from '../../services/sentiment.service';

@Component({
  selector: 'app-text-tab',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <div class="text-container">
      <textarea
        [(ngModel)]="inputText"
        placeholder="Introduceți textul pentru analiză..."
      >
      </textarea>

      <div class="language-selection">
        <label for="text-language">Limba (opțional):</label>
        <select id="text-language" [(ngModel)]="selectedLanguage">
          <option value="">Auto-detectare</option>
          <option value="ro">Română</option>
          <option value="en">Engleză</option>
          <option value="fr">Franceză</option>
          <option value="de">Germană</option>
          <option value="es">Spaniolă</option>
          <option value="it">Italiană</option>
        </select>
      </div>

      <button
        class="action-btn"
        [disabled]="!inputText.trim()"
        (click)="analyzeText()"
      >
        Analizează Text
      </button>
    </div>
  `,
  styleUrls: ['./text-tab.component.css'],
})
export class TextTabComponent {
  @Output() analyzeResults = new EventEmitter<any>();

  inputText = '';
  selectedLanguage = '';

  constructor(private sentimentService: SentimentService) {}

  analyzeText(): void {
    if (!this.inputText.trim()) return;

    this.sentimentService
      .analyzeText(this.inputText, this.selectedLanguage)
      .subscribe({
        next: (result) => {
          this.analyzeResults.emit(result);
          // Nu resetăm textul pentru a permite utilizatorului să facă modificări
        },
        error: (error) => {
          this.analyzeResults.emit({
            error: error.message || 'Eroare la analiza textului',
          });
        },
      });
  }
}
