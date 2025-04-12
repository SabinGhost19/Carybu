import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-loading-overlay',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="overlay" *ngIf="isLoading">
      <div class="spinner"></div>
      <p>Se procesează...</p>
    </div>
  `,
  styleUrls: ['./loading-overlay.component.css'],
})
export class LoadingOverlayComponent {
  @Input() isLoading = false;
}
