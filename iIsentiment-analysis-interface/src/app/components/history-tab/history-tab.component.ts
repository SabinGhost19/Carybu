import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SentimentService } from '../../services/sentiment.service';

@Component({
  selector: 'app-history-tab',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="history-container">
      <h2>Istoric Analize</h2>
      <div
        class="history-list"
        *ngIf="historyItems.length > 0; else emptyHistory"
      >
        <div
          class="history-item"
          *ngFor="let item of historyItems"
          (click)="selectHistoryItem(item.id)"
        >
          <div class="history-file-info">
            <i class="fas" [ngClass]="getFileIcon(item)"></i>
            <div>
              <div class="file-name">
                {{ item.fileName || 'Text introdus' }}
              </div>
              <div class="history-date">{{ formatDate(item.uploadedAt) }}</div>
            </div>
          </div>
          <div
            class="sentiment-badge"
            [ngClass]="'sentiment-' + (item.overallSentiment || 'neutral')"
          >
            {{ formatSentiment(item.overallSentiment) }}
          </div>
        </div>
      </div>

      <ng-template #emptyHistory>
        <p class="empty-history">
          {{ errorMessage || 'Nicio analiză în istoric' }}
        </p>
      </ng-template>
    </div>
  `,
  styleUrls: ['./history-tab.component.css'],
})
export class HistoryTabComponent implements OnInit {
  @Output() historyItemSelected = new EventEmitter<any>();

  historyItems: any[] = [];
  errorMessage = '';

  constructor(private sentimentService: SentimentService) {}

  ngOnInit(): void {
    this.sentimentService.historyData.subscribe((data) => {
      this.historyItems = data;
      this.errorMessage = '';
    });

    this.sentimentService.loadHistory();
  }

  selectHistoryItem(id: string): void {
    this.sentimentService.getHistoryItem(id).subscribe({
      next: (result) => {
        this.historyItemSelected.emit(result);
      },
      error: (error) => {
        this.historyItemSelected.emit({
          error: error.message || 'Eroare la încărcarea detaliilor',
        });
      },
    });
  }

  getFileIcon(item: any): string {
    if (
      item.contentType?.includes('pdf') ||
      (item.fileName && item.fileName.endsWith('.pdf'))
    ) {
      return 'fa-file-pdf';
    } else if (
      item.contentType?.includes('word') ||
      (item.fileName &&
        (item.fileName.endsWith('.docx') || item.fileName.endsWith('.doc')))
    ) {
      return 'fa-file-word';
    } else if (
      item.contentType?.includes('text') ||
      (item.fileName && item.fileName.endsWith('.txt'))
    ) {
      return 'fa-file-lines';
    }
    return 'fa-file-alt';
  }

  formatDate(dateStr: string): string {
    const date = new Date(dateStr);
    return `${date.toLocaleDateString()} ${date.toLocaleTimeString()}`;
  }

  formatSentiment(sentiment: string): string {
    if (!sentiment) return 'Neutru';
    return sentiment.charAt(0).toUpperCase() + sentiment.slice(1);
  }
}
