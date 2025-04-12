import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { finalize } from 'rxjs/operators';

@Injectable({
  providedIn: 'root',
})
export class SentimentService {
  private API_BASE_URL = 'http://localhost:3000/api';
  public loadingState = new BehaviorSubject<boolean>(false);
  public historyData = new BehaviorSubject<any[]>([]);

  constructor(private http: HttpClient) {}

  setLoading(isLoading: boolean): void {
    this.loadingState.next(isLoading);
  }

  uploadFile(file: File, language?: string): Observable<any> {
    const formData = new FormData();
    formData.append('file', file);

    if (language) {
      formData.append('language', language);
    }

    this.setLoading(true);
    return this.http
      .post(`${this.API_BASE_URL}/files/upload`, formData)
      .pipe(finalize(() => this.setLoading(false)));
  }

  analyzeText(text: string, language?: string): Observable<any> {
    const body = {
      text,
      language: language || undefined,
    };

    this.setLoading(true);
    return this.http
      .post(`${this.API_BASE_URL}/files/analyze-text`, body)
      .pipe(finalize(() => this.setLoading(false)));
  }

  loadHistory(): void {
    this.setLoading(true);
    this.http
      .get<any[]>(`${this.API_BASE_URL}/history`)
      .pipe(finalize(() => this.setLoading(false)))
      .subscribe({
        next: (data) => this.historyData.next(data),
        error: (error) => console.error('Error loading history:', error),
      });
  }

  getHistoryItem(id: string): Observable<any> {
    this.setLoading(true);
    return this.http
      .get(`${this.API_BASE_URL}/history/${id}`)
      .pipe(finalize(() => this.setLoading(false)));
  }
}
