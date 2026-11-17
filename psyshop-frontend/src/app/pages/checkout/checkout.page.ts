
import {
  Component,
  ChangeDetectorRef,
  inject
} from '@angular/core';

import { CommonModule, CurrencyPipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';

import {
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButton,
  IonIcon,
  IonInput,
  IonTextarea,
  IonText,
  IonSpinner
} from '@ionic/angular';

import { CartService } from '../../services/cart.service';

import {
  OrderService,
  OrderRequest
} from '../../services/order.service';

import { AuthService } from '../../services/auth.service';
import { ToastService } from '../../services/toast';

@Component({
  selector: 'app-checkout',
  templateUrl: './checkout.page.html',
  styleUrls: ['./checkout.page.scss'],
  standalone: true,
  imports: [
    CommonModule,
    CurrencyPipe,
    FormsModule,
    RouterLink,
    IonHeader,
    IonToolbar,
    IonTitle,
    IonContent,
    IonButton,
    IonIcon,
    IonInput,
    IonTextarea,
    IonText,
    IonSpinner
  ]
})
export class CheckoutPage {

  private cartService = inject(CartService);
  private orderService = inject(OrderService);
  private authService = inject(AuthService);
  private toastService = inject(ToastService);
  private router = inject(Router);
  private cdr = inject(ChangeDetectorRef);

  items = this.cartService.items;

  customer_name = '';
  customer_email = '';
  customer_phone = '';
  delivery_address = '';

  submitting = false;
  errorMessage = '';

  precioSubtotal(
    precio: string | number,
    cantidad: number
  ): number {
    return Number(precio) * cantidad;
  }

  get total(): number {
    return this.items.reduce(
      (sum, item) =>
        sum + Number(item.game.price) * item.quantity,
      0
    );
  }

  async ionViewWillEnter(): Promise<void> {

    if (!this.authService.isLoggedIn()) {

      await this.toastService.mostrar(
        'Para comprar en PsyShop, inicia sesión o crea una cuenta.',
        'warning'
      );

      this.router.navigate(['/tabs/perfil']);
    }
  }

  async enviarPedido(): Promise<void> {

    this.errorMessage = '';

    // Verificar la sesión antes de enviar el pedido.
    if (!this.authService.isLoggedIn()) {

      await this.toastService.mostrar(
        'Debes iniciar sesión o registrarte antes de comprar.',
        'warning'
      );

      this.router.navigate(['/tabs/perfil']);
      return;
    }

    // Verificar que el carrito tenga productos.
    if (this.items.length === 0) {
      this.errorMessage = 'Tu carrito está vacío.';
      return;
    }

    // Verificar que todos los campos estén completos.
    if (
      !this.customer_name.trim() ||
      !this.customer_email.trim() ||
      !this.customer_phone.trim() ||
      !this.delivery_address.trim()
    ) {
      this.errorMessage = 'Completa todos los campos.';
      return;
    }

    // Validar el correo electrónico.
    const emailValido =
      /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
        this.customer_email
      );

    if (!emailValido) {
      this.errorMessage =
        'Introduce un correo electrónico válido.';
      return;
    }

    // Preparar los datos del pedido.
    const data: OrderRequest = {
      customer_name: this.customer_name.trim(),
      customer_email: this.customer_email.trim(),
      customer_phone: this.customer_phone.trim(),
      delivery_address: this.delivery_address.trim(),

      items: this.items.map(item => ({
        game_id: item.game.id,
        quantity: item.quantity
      }))
    };

    this.submitting = true;

    // Enviar el pedido al backend.
    this.orderService.createOrder(data).subscribe({

      next: async (response) => {

        this.cartService.clearCart();

        this.submitting = false;

        this.cdr.detectChanges();

        // Notificación de pedido registrado.
        await this.toastService.mostrar(
          `¡Pedido registrado! Tu número de pedido es ${response.order.id}.`,
          'success'
        );

        this.router.navigate(['/tabs/pedidos']);
      },

      error: (error) => {

        console.error(
          'Error al registrar pedido:',
          error
        );

        this.submitting = false;

        if (error.status === 401) {

          this.authService.clearSession();

          this.errorMessage =
            'Tu sesión expiró. Inicia sesión nuevamente para comprar.';

          this.router.navigate(['/tabs/perfil']);

        } else if (error.status === 422) {

          this.errorMessage =
            error.error?.message ??
            'Revisa los datos y la disponibilidad de los videojuegos.';

        } else {

          this.errorMessage =
            'No se pudo registrar el pedido. Verifica la conexión e inténtalo de nuevo.';
        }

        this.cdr.detectChanges();
      }
    });
  }
}
