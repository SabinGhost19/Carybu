import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { UploadTabComponent } from './components/upload-tab/upload-tab.component';
import { TextTabComponent } from './components/text-tab/text-tab.component';
import { HistoryTabComponent } from './components/history-tab/history-tab.component';
import { ResultsComponent } from './components/results/results.component';
import { LoadingOverlayComponent } from './components/loading-overlay/loading-overlay.component';
import { SentimentService } from './services/sentiment.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    UploadTabComponent,
    TextTabComponent,
    HistoryTabComponent,
    ResultsComponent,
    LoadingOverlayComponent,
  ],
  template: `
    <div class="container">
      <header>
        <h1>Sentiment Analysis</h1>
        <p>Upload a file or enter text to analyze sentiments</p>
      </header>

      <div class="tabs">
        <button
          class="tab-btn"
          [class.active]="activeTab === 'upload'"
          (click)="setActiveTab('upload')"
        >
          File Upload
        </button>
        <button
          class="tab-btn"
          [class.active]="activeTab === 'text'"
          (click)="setActiveTab('text')"
        >
          Text Input
        </button>
        <button
          class="tab-btn"
          [class.active]="activeTab === 'history'"
          (click)="setActiveTab('history')"
        >
          History
        </button>
      </div>

      <div class="tab-content" [class.active]="activeTab === 'upload'">
        <app-upload-tab
          *ngIf="activeTab === 'upload'"
          (analyzeResults)="showResults($event)"
        >
        </app-upload-tab>
      </div>

      <div class="tab-content" [class.active]="activeTab === 'text'">
        <app-text-tab
          *ngIf="activeTab === 'text'"
          (analyzeResults)="showResults($event)"
        >
        </app-text-tab>
      </div>

      <div class="tab-content" [class.active]="activeTab === 'history'">
        <app-history-tab
          *ngIf="activeTab === 'history'"
          (historyItemSelected)="showResults($event)"
        >
        </app-history-tab>
      </div>

      <app-results *ngIf="showResultsSection" [resultData]="resultData">
      </app-results>
    </div>

    <app-loading-overlay [isLoading]="isLoading"></app-loading-overlay>
  `,
  styleUrls: ['./app.component.css'],
})
export class AppComponent {
  activeTab = 'upload';
  isLoading = false;
  showResultsSection = false;
  resultData: any = null;

  constructor(private sentimentService: SentimentService) {
    this.sentimentService.loadingState.subscribe((state) => {
      this.isLoading = state;
    });
  }

  setActiveTab(tab: string): void {
    this.activeTab = tab;
    if (tab === 'history') {
      this.sentimentService.loadHistory();
    }
  }

  showResults(data: any): void {
    this.resultData = data;
    this.showResultsSection = true;
    setTimeout(() => {
      const resultsElement = document.querySelector('app-results');
      if (resultsElement) {
        resultsElement.scrollIntoView({ behavior: 'smooth' });
      }
    }, 100);
  }
}
