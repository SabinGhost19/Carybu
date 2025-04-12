import { Component, EventEmitter, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { SentimentService } from '../../services/sentiment.service';

@Component({
  selector: 'app-upload-tab',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <div class="upload-container">
      <div
        class="upload-area"
        [class.drag-over]="isDragOver"
        (dragenter)="onDragEnter($event)"
        (dragover)="onDragOver($event)"
        (dragleave)="onDragLeave($event)"
        (drop)="onDrop($event)"
      >
        <ng-container *ngIf="!selectedFile; else fileSelected">
          <i class="fas fa-cloud-upload-alt"></i>
          <p>Drag file here or</p>
          <label for="file-input" class="file-label">Select a file</label>
          <input
            type="file"
            id="file-input"
            accept=".txt,.docx,.pdf,.rtf"
            (change)="onFileSelected($event)"
          />
          <p class="file-types">Supported formats: .txt, .docx, .pdf, .rtf</p>
        </ng-container>

        <ng-template #fileSelected>
          <i class="fas fa-file-alt"></i>
          <p class="file-name">{{ selectedFile?.name }}</p>
          <p>File selected</p>
          <label for="file-input" class="file-label">Change file</label>
        </ng-template>
      </div>

      <div class="language-selection">
        <label for="file-language">Language (optional):</label>
        <select id="file-language" [(ngModel)]="selectedLanguage">
          <option value="">Auto-detect</option>
          <option value="ro">Romanian</option>
          <option value="en">English</option>
          <option value="fr">French</option>
          <option value="de">German</option>
          <option value="es">Spanish</option>
          <option value="it">Italian</option>
        </select>
      </div>

      <button
        class="action-btn"
        [disabled]="!selectedFile"
        (click)="uploadFile()"
      >
        Analyze File
      </button>
    </div>
  `,
  styleUrls: ['./upload-tab.component.css'],
})
export class UploadTabComponent {
  @Output() analyzeResults = new EventEmitter<any>();

  isDragOver = false;
  selectedFile: File | null = null;
  selectedLanguage = '';

  constructor(private sentimentService: SentimentService) {}

  onDragEnter(event: Event): void {
    this.preventDefault(event);
    this.isDragOver = true;
  }

  onDragOver(event: Event): void {
    this.preventDefault(event);
    this.isDragOver = true;
  }

  onDragLeave(event: Event): void {
    this.preventDefault(event);
    this.isDragOver = false;
  }

  onDrop(event: DragEvent): void {
    this.preventDefault(event);
    this.isDragOver = false;

    if (event.dataTransfer?.files.length) {
      this.handleFiles(event.dataTransfer.files);
    }
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files?.length) {
      this.handleFiles(input.files);
    }
  }

  handleFiles(files: FileList): void {
    this.selectedFile = files[0];
  }

  uploadFile(): void {
    if (!this.selectedFile) return;

    this.sentimentService
      .uploadFile(this.selectedFile, this.selectedLanguage)
      .subscribe({
        next: (result) => {
          this.analyzeResults.emit(result);
          this.resetForm();
        },
        error: (error) => {
          this.analyzeResults.emit({
            error: error.message || 'Error uploading file',
          });
        },
      });
  }

  resetForm(): void {
    this.selectedFile = null;
    // keep language for next upload
  }

  private preventDefault(event: Event): void {
    event.preventDefault();
    event.stopPropagation();
  }
}
