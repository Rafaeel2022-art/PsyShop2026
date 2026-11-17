
import {
  Component,
  OnInit,
  OnDestroy,
  inject,
  ChangeDetectorRef
} from '@angular/core';

import { CurrencyPipe } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { Subscription } from 'rxjs';
import { AuthService } from '../services/auth.service';
import { ToastService } from '../services/toast';

import {
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonImg,
  IonSpinner,
  IonText,
  IonSearchbar,
  IonSelect,
  IonSelectOption,
  IonButton,
  IonIcon,
  IonButtons,
  IonBadge
} from '@ionic/angular';

import { GameService, Game } from '../services/game';
import { CartService } from '../services/cart.service';

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
  standalone: true,
  imports: [
    IonHeader,
    IonToolbar,
    IonTitle,
    IonContent,
    IonImg,
    IonSpinner,
    IonText,
    IonSearchbar,
    IonSelect,
    IonSelectOption,
    IonButton,
    IonIcon,
    IonButtons,
    IonBadge,
    CurrencyPipe,
    RouterLink
  ]
})
export class HomePage implements OnInit, OnDestroy {
private gameService = inject(GameService);
private cdr = inject(ChangeDetectorRef);
private cartService = inject(CartService);
private authService = inject(AuthService);
private router = inject(Router);
private toastService = inject(ToastService);

  private cartSubscription?: Subscription;

  games: Game[] = [];
  loading = true;
  errorMessage = '';

  searchTerm = '';
  selectedPlatform = '';
  selectedGenre = '';

  totalItemsCarrito = 0;

  ngOnInit(): void {
    // Mantener actualizado el contador del carrito.
    this.cartSubscription = this.cartService.cart$.subscribe(() => {
      this.totalItemsCarrito = this.cartService.totalItems;
      this.cdr.detectChanges();
    });

    // Cargar videojuegos desde Laravel.
    this.gameService.getGames().subscribe({
      next: (response) => {
        this.games = response.data;
        this.loading = false;
        this.cdr.detectChanges();
      },
      error: (error) => {
        console.error('Error al cargar videojuegos:', error);

        this.errorMessage =
          'No se pudieron cargar los videojuegos. Verifica la conexión con el servidor.';

        this.loading = false;
        this.cdr.detectChanges();
      }
    });
  }

  ngOnDestroy(): void {
    this.cartSubscription?.unsubscribe();
  }

 
async agregarAlCarrito(game: Game): Promise<void> {

  // Comprobar si el cliente inició sesión
  if (!this.authService.isLoggedIn()) {

    await this.toastService.mostrar(
      'Debes iniciar sesión para agregar videojuegos al carrito.',
      'warning'
    );

    this.router.navigate(['/tabs/perfil'], {
      queryParams: {
        mode: 'login'
      }
    });

    return;
  }

  // Agregar videojuego al carrito
  const agregado = this.cartService.addToCart(game);

  if (agregado) {
    await this.toastService.mostrar(
      `${game.title} se agregó al carrito.`,
      'success'
    );
  } else {
    await this.toastService.mostrar(
      'No hay suficiente stock disponible para agregar este videojuego.',
      'danger'
    );
  }
}


  get platforms(): string[] {
    return [
      ...new Set(
        this.games
          .map(game => game.platform)
          .filter(
            (platform): platform is string =>
              platform !== null && platform !== ''
          )
      )
    ].sort();
  }

  get genres(): string[] {
    return [
      ...new Set(
        this.games
          .map(game => game.genre)
          .filter(
            (genre): genre is string =>
              genre !== null && genre !== ''
          )
      )
    ].sort();
  }

  get filteredGames(): Game[] {
    const term = this.searchTerm.trim().toLowerCase();

    return this.games.filter(game => {
      const matchesSearch =
        !term ||
        game.title.toLowerCase().includes(term) ||
        (game.description ?? '').toLowerCase().includes(term);

      const matchesPlatform =
        !this.selectedPlatform ||
        game.platform === this.selectedPlatform;

      const matchesGenre =
        !this.selectedGenre ||
        game.genre === this.selectedGenre;

      return matchesSearch && matchesPlatform && matchesGenre;
    });
  }

  onSearch(event: CustomEvent): void {
    this.searchTerm = String(event.detail?.value ?? '');
  }

  onPlatformChange(event: CustomEvent): void {
    this.selectedPlatform = String(event.detail?.value ?? '');
  }

  onGenreChange(event: CustomEvent): void {
    this.selectedGenre = String(event.detail?.value ?? '');
  }

  clearFilters(): void {
    this.searchTerm = '';
    this.selectedPlatform = '';
    this.selectedGenre = '';
  }

  explorarCatalogo(): void {
    document.getElementById('catalogo')?.scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });
  }
}
