// app.component.ts
import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from 'src/environments/environment.prod';
interface SentimentResult {
  documentSentiment: string;
  confidenceScores: {
    positive: number;
    neutral: number;
    negative: number;
  };
  sentences: {
    text: string;
    sentiment: string;
    confidenceScores: {
      positive: number;
      neutral: number;
      negative: number;
    };
  }[];
}

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css'],
})
export class AppComponent {
  text: string = '';
  language: string = 'ro';
  loading: boolean = false;
  result: SentimentResult | null = null;
  error: string | null = null;
  activeTab: string = 'single';

  batchTexts: { id: string; text: string; language: string }[] = [
    { id: '1', text: '', language: 'ro' },
  ];
  batchResults: any[] = [];
  batchLoading: boolean = false;
  batchError: string | null = null;
  //baze api...modified here for production
  private readonly API_BASE_URL = environment.backendUrl;

  constructor(private http: HttpClient) {}

  analyzeSentiment() {
    if (!this.text) {
      this.error = 'Textul este obligatoriu';
      return;
    }

    this.loading = true;
    this.result = null;
    this.error = null;

    this.http
      .post<SentimentResult>(`${this.API_BASE_URL}/analyze-sentiment`, {
        text: this.text,
        language: this.language,
      })
      .subscribe(
        (response) => {
          this.result = response;
          this.loading = false;
        },
        (error) => {
          this.error =
            'Eroare la analiza sentimentelor: ' +
            (error.error?.error || error.message);
          this.loading = false;
        }
      );
  }

  addBatchText() {
    this.batchTexts.push({
      id: (this.batchTexts.length + 1).toString(),
      text: '',
      language: 'ro',
    });
  }

  removeBatchText(index: number) {
    this.batchTexts.splice(index, 1);
  }

  analyzeBatchSentiment() {
    const validTexts = this.batchTexts.filter(
      (item) => item.text.trim().length > 0
    );

    if (validTexts.length === 0) {
      this.batchError = 'Cel puțin un text este obligatoriu';
      return;
    }

    this.batchLoading = true;
    this.batchResults = [];
    this.batchError = null;

    this.http
      .post<any[]>(`${this.API_BASE_URL}/analyze-sentiment-batch`, {
        documents: validTexts,
      })
      .subscribe(
        (response) => {
          this.batchResults = response;
          this.batchLoading = false;
        },
        (error) => {
          this.batchError =
            'Eroare la analiza sentimentelor în batch: ' +
            (error.error?.error || error.message);
          this.batchLoading = false;
        }
      );
  }

  getSentimentColor(sentiment: string): string {
    switch (sentiment) {
      case 'positive':
        return 'green';
      case 'negative':
        return 'red';
      case 'neutral':
        return 'gray';
      case 'mixed':
        return 'orange';
      default:
        return 'black';
    }
  }
}
