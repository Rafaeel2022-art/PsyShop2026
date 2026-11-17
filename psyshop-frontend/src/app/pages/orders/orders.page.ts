
import {
  Component,
  OnInit,
  ChangeDetectorRef,
  inject
} from '@angular/core';

import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';

import {
  IonContent,
  IonHeader,
  IonTitle,
  IonToolbar,
  IonIcon,
  IonCard,
  IonCardContent,
  IonButton,
  IonBadge,
  IonSpinner,
  IonText
} from '@ionic/angular';

import { addIcons } from 'ionicons';
import {
  receiptOutline,
  timeOutline,
  checkmarkCircleOutline,
  closeCircleOutline,
  bagHandleOutline,
  gameControllerOutline,
  arrowForwardOutline,
  refreshOutline
} from 'ionicons/icons';

import { AuthService } from '../../services/auth.service';
import {
  OrderService,
  CustomerOrder
} from '../../services/order.service';

@Component({
  selector: 'app-orders',
  templateUrl: './orders.page.html',
  styleUrls: ['./orders.page.scss'],
  standalone: true,
  imports: [
    CommonModule,
    IonContent,
    IonHeader,
    IonTitle,
    IonToolbar,
    IonIcon,
    IonCard,
    IonCardContent,
    IonButton,
    IonBadge,
    IonSpinner,
    IonText,
    RouterLink
  ]
})
export class OrdersPage implements OnInit {
  readonly auth = inject(AuthService);
  private orderService = inject(OrderService);
  private router = inject(Router);
  private cdr = inject(ChangeDetectorRef);

  orders: CustomerOrder[] = [];
  loading = false;
  errorMessage = '';

  totalOrders = 0;
  pendingOrders = 0;
  completedOrders = 0;

  constructor() {
    addIcons({
      receiptOutline,
      timeOutline,
      checkmarkCircleOutline,
      closeCircleOutline,
      bagHandleOutline,
      gameControllerOutline,
      arrowForwardOutline,
      refreshOutline
    });
  }

  ngOnInit(): void {
    this.loadOrders();
  }

  ionViewWillEnter(): void {
    this.loadOrders();
  }

 
loadOrders(): void {
  if (!this.auth.isLoggedIn()) {
    this.orders = [];
    this.resetStats();
    this.loading = false;
    this.errorMessage = '';
    this.cdr.detectChanges();
    return;
  }

  this.loading = true;
  this.errorMessage = '';
  this.cdr.detectChanges();

  this.orderService.getMyOrders().subscribe({
    next: (response) => {
      this.orders = response.data ?? [];
      this.totalOrders = response.total ?? this.orders.length;

      this.pendingOrders = this.orders.filter(
        order => order.status === 'pending'
      ).length;

      this.completedOrders = this.orders.filter(
        order => order.status === 'completed'
      ).length;

      this.loading = false;
      this.cdr.detectChanges();
    },
    error: (error) => {
      console.error('Error al cargar mis pedidos:', error);

      this.orders = [];
      this.resetStats();
      this.loading = false;

      if (error.status === 401) {
        this.auth.clearSession();
        this.errorMessage =
          'Tu sesión expiró. Inicia sesión para consultar tus compras.';
      } else {
        this.errorMessage =
          'No se pudo cargar tu historial. Verifica la conexión e inténtalo de nuevo.';
      }

      this.cdr.detectChanges();
    }
  });
}


  private resetStats(): void {
    this.totalOrders = 0;
    this.pendingOrders = 0;
    this.completedOrders = 0;
  }

  statusLabel(status: string): string {
  const labels: Record<string, string> = {
    pending: 'Pendiente',
    confirmed: 'Confirmado',
    preparing: 'En preparación',
    shipped: 'Enviado',
    completed: 'Completado',
    cancelled: 'Cancelado'
  };

  return labels[status] ?? status;
}

  statusColor(status: string): string {
  const colors: Record<string, string> = {
    pending: 'warning',
    confirmed: 'primary',
    preparing: 'tertiary',
    shipped: 'secondary',
    completed: 'success',
    cancelled: 'danger'
  };

  return colors[status] ?? 'medium';
}

  formatDate(date: string): string {
    return new Date(date).toLocaleString('es-SV', {
      dateStyle: 'medium',
      timeStyle: 'short'
    });
  }

  formatPrice(value: string | number): number {
    return Number(value);
  }

  trackOrder(index: number, order: CustomerOrder): number {
    return order.id;
  }
}
