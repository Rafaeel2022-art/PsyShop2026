
import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NavigationEnd, Router, RouterLink } from '@angular/router';
import { filter, Observable } from 'rxjs';

import { addIcons } from 'ionicons';
import {
  logOutOutline,
  homeOutline,
  gameControllerOutline,
  cartOutline,
  receiptOutline,
  personCircleOutline,
  logoWhatsapp
} from 'ionicons/icons';

import { IonTabs } from '@ionic/angular/ion-tabs';
import { IonHeader } from '@ionic/angular/ion-header';
import { IonToolbar } from '@ionic/angular/ion-toolbar';
import { IonTitle } from '@ionic/angular/ion-title';
import { IonTabBar } from '@ionic/angular/ion-tab-bar';
import { IonTabButton } from '@ionic/angular/ion-tab-button';
import { IonIcon } from '@ionic/angular/ion-icon';
import { IonLabel } from '@ionic/angular/ion-label';
import { IonRouterOutlet } from '@ionic/angular/ion-router-outlet';
import { IonButtons, IonButton } from '@ionic/angular';

import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-tabs',
  templateUrl: './tabs.page.html',
  styleUrls: ['./tabs.page.scss'],
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    IonButtons,
    IonTabs,
    IonHeader,
    IonToolbar,
    IonTitle,
    IonRouterOutlet,
    IonTabBar,
    IonTabButton,
    IonIcon,
    IonLabel,
    IonButton
  ]
})
export class TabsPage {
  pageTitle = 'Inicio';
  authState$!: Observable<boolean>;
  logoutLoading = false;

  private readonly tabTitles: Record<string, string> = {
    inicio: 'Inicio',
    catalogo: 'Catálogo',
    carrito: 'Mi carrito',
    pedidos: 'Mis pedidos',
    perfil: 'Mi perfil'
  };

  constructor(
    private router: Router,
    private auth: AuthService
  ) {
    addIcons({
      logOutOutline,
      homeOutline,
      gameControllerOutline,
      cartOutline,
      receiptOutline,
      personCircleOutline,
      logoWhatsapp
    });

    this.authState$ = this.auth.authState$;

    this.updateTitle(this.router.url);

    this.router.events
      .pipe(filter(event => event instanceof NavigationEnd))
      .subscribe(event => {
        this.updateTitle(event.urlAfterRedirects);
      });
  }

  private updateTitle(url: string): void {
    const match = url.match(/\/tabs\/([^/?#]+)/);
    const tab = match?.[1] ?? 'inicio';

    this.pageTitle = this.tabTitles[tab] ?? 'Inicio';
  }

  logout(): void {
    if (this.logoutLoading) {
      return;
    }

    this.logoutLoading = true;

    this.auth.logout().subscribe({
      next: () => {
        this.logoutLoading = false;
        this.router.navigate(['/tabs/inicio']);
      },
      error: () => {
        this.logoutLoading = false;
      }
    });
  }
}
