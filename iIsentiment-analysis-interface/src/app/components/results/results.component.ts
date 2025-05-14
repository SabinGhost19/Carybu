import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-results',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="results">
      <h2>Rezultate</h2>

      <div *ngIf="resultData">
        <div class="result-card" *ngIf="!resultData.error; else errorDisplay">
          <div class="result-header">
            <div class="file-info">
              <i class="fas" [ngClass]="getFileIcon()"></i>
              <div class="file-name">
                {{ resultData.fileName || 'Text introdus' }}
              </div>
            </div>
            <div
              class="sentiment-badge"
              [ngClass]="
                'sentiment-' +
                (resultData.sentimentResult?.documentSentiment || 'neutral')
              "
            >
              {{
                formatSentiment(resultData.sentimentResult?.documentSentiment)
              }}
            </div>
          </div>

          <div class="confidence-scores">
            <div class="score-item">
              <div class="score-label">Pozitiv</div>
              <div class="score-value positive">
                {{
                  formatScore(
                    resultData.sentimentResult?.confidenceScores?.positive
                  )
                }}
              </div>
            </div>
            <div class="score-item">
              <div class="score-label">Neutru</div>
              <div class="score-value neutral">
                {{
                  formatScore(
                    resultData.sentimentResult?.confidenceScores?.neutral
                  )
                }}
              </div>
            </div>
            <div class="score-item">
              <div class="score-label">Negativ</div>
              <div class="score-value negative">
                {{
                  formatScore(
                    resultData.sentimentResult?.confidenceScores?.negative
                  )
                }}
              </div>
            </div>
          </div>

          <ng-container *ngIf="resultData.extractedText">
            <h3>Text analizat</h3>
            <div class="extracted-text">{{ resultData.extractedText }}</div>
            <div class="text-preview" *ngIf="resultData.textLength > 1000">
              (Fragment din textul complet de
              {{ resultData.textLength }} caractere)
            </div>
          </ng-container>

          <ng-container *ngIf="resultData.sentimentResult?.sentences?.length">
            <h3>Analiza pe propoziții</h3>
            <div class="sentences-analysis">
              <div
                class="sentence-item"
                *ngFor="
                  let sentence of resultData.sentimentResult.sentences.slice(
                    0,
                    5
                  )
                "
              >
                <div class="sentence-text">"{{ sentence.text }}"</div>
                <div
                  class="sentence-sentiment sentiment-badge"
                  [ngClass]="'sentiment-' + sentence.sentiment"
                >
                  {{ formatSentiment(sentence.sentiment) }}
                </div>
              </div>

              <div
                class="text-preview"
                *ngIf="resultData.sentimentResult.sentences.length > 5"
              >
                (Sunt afișate primele 5 din
                {{ resultData.sentimentResult.sentences.length }} propoziții)
              </div>
            </div>
          </ng-container>

          <div class="download-section" *ngIf="resultData.blobUrl">
            <a
              [href]="getApiUrl() + '/files/download/' + resultData.id"
              class="action-btn"
              download
            >
              <i class="fas fa-download"></i> Descarcă fișierul
            </a>
          </div>
        </div>

        <ng-template #errorDisplay>
          <div class="result-card error-card">
            <h3>Eroare</h3>
            <p>{{ resultData.error }}</p>
          </div>
        </ng-template>
      </div>
    </div>
  `,
  styleUrls: ['./results.component.css'],
})
export class ResultsComponent implements OnChanges {
  @Input() resultData: any = null;

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['resultData'] && changes['resultData'].currentValue) {
      // Procesare date dacă este necesar
    }
  }

  getFileIcon(): string {
    if (!this.resultData) return 'fa-file-alt';

    if (
      this.resultData.contentType?.includes('pdf') ||
      (this.resultData.fileName && this.resultData.fileName.endsWith('.pdf'))
    ) {
      return 'fa-file-pdf';
    } else if (
      this.resultData.contentType?.includes('word') ||
      (this.resultData.fileName &&
        (this.resultData.fileName.endsWith('.docx') ||
          this.resultData.fileName.endsWith('.doc')))
    ) {
      return 'fa-file-word';
    } else if (
      this.resultData.contentType?.includes('text') ||
      (this.resultData.fileName && this.resultData.fileName.endsWith('.txt'))
    ) {
      return 'fa-file-lines';
    }

    return 'fa-file-alt';
  }

  formatScore(score: number): string {
    if (score === undefined || score === null) return '0%';
    return Math.round(score * 100) + '%';
  }

  formatSentiment(sentiment: string): string {
    if (!sentiment) return 'Neutru';
    return sentiment.charAt(0).toUpperCase() + sentiment.slice(1);
  }

  getApiUrl(): string {
    return (window as any)['environment']?.apiUrl;
  }
}
