
import {
  Component,
  OnInit,
  ChangeDetectorRef
} from '@angular/core';

import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';

import {
  IonContent,
  IonButton,
  IonIcon
} from '@ionic/angular';

import { addIcons } from 'ionicons';

import {
  logoWhatsapp,
  arrowForwardOutline,
  gameControllerOutline,
  shieldCheckmarkOutline,
  albumsOutline,
  flashOutline,
  footballOutline,
  carSportOutline,
  compassOutline,
  trophyOutline
} from 'ionicons/icons';

import { GameService, Game } from '../../services/game';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.page.html',
  styleUrls: ['./dashboard.page.scss'],
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    IonContent,
    IonButton,
    IonIcon
  ]
})
export class DashboardPage implements OnInit {

  featuredGames: Game[] = [];

  loadingFeatured = true;
  featuredError = false;

  constructor(
    private gameService: GameService,
    private cdr: ChangeDetectorRef
  ) {
    addIcons({
      logoWhatsapp,
      arrowForwardOutline,
      gameControllerOutline,
      shieldCheckmarkOutline,
      albumsOutline,
      flashOutline,
      footballOutline,
      carSportOutline,
      compassOutline,
      trophyOutline
    });
  }

  ngOnInit(): void {
    console.log('PsyShop: Dashboard iniciado');

    this.loadFeaturedGames();
  }

  loadFeaturedGames(): void {

    console.log('PsyShop: cargando videojuegos destacados');

    this.loadingFeatured = true;
    this.featuredError = false;

    this.gameService.getGames().subscribe({

      next: (response) => {

        this.featuredGames = response.data
          .filter(game => game.is_active && game.stock > 0)
          .slice(0, 6);

        this.loadingFeatured = false;
        this.featuredError = false;

        console.log('PsyShop: carga finalizada');
        console.log('loadingFeatured:', this.loadingFeatured);
        console.log('Cantidad de juegos:', this.featuredGames.length);

        this.cdr.detectChanges();
      },

      error: (error) => {

        console.error('PsyShop: error al cargar:', error);

        this.featuredError = true;
        this.loadingFeatured = false;

        this.cdr.detectChanges();
      }

    });
  }

  formatPrice(price: string | number): string {
    return Number(price).toFixed(2);
  }

}
