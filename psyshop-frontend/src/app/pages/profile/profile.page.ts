import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import {
  IonContent,
  IonHeader,
  IonTitle,
  IonToolbar,
  IonCard,
  IonCardContent,
  IonInput,
  IonButton,
  IonText,
  IonSpinner,
  IonIcon,
  IonSegment,
  IonSegmentButton,
  IonLabel,
} from '@ionic/angular';

import { addIcons } from 'ionicons';
import {
  personCircleOutline,
  mailOutline,
  lockClosedOutline,
  logInOutline,
  personAddOutline,
  logOutOutline,
  shieldCheckmarkOutline, saveOutline } from 'ionicons/icons';

import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.page.html',
  styleUrls: ['./profile.page.scss'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    IonContent,
    IonHeader,
    IonTitle,
    IonToolbar,
    IonCard,
    IonCardContent,
    IonInput,
    IonButton,
    IonText,
    IonSpinner,
    IonIcon,
    IonSegment,
    IonSegmentButton,
    IonLabel,
  ],
})
export class ProfilePage implements OnInit {

  mode: 'login' | 'register' = 'login';

  name = '';
  email = '';
  password = '';
  passwordConfirmation = '';

  // Edición del perfil
  editName = '';
  editEmail = '';

  // Cambio de contraseña
  currentPassword = '';
  newPassword = '';
  newPasswordConfirmation = '';

  loading = false;
  errorMessage = '';
  successMessage = '';

  user = this.auth.getUser();

  constructor(
    private auth: AuthService,
    private router: Router,
    private cdr: ChangeDetectorRef,
    private route: ActivatedRoute
  ) {
    addIcons({personCircleOutline,shieldCheckmarkOutline,saveOutline,lockClosedOutline,logOutOutline,mailOutline,logInOutline,personAddOutline,});
  }

  ngOnInit(): void {

    this.user = this.auth.getUser();

    if (this.user) {
      this.editName = this.user.name;
      this.editEmail = this.user.email;
    }

    // Sincronizar el perfil cuando cambia la sesión
    this.auth.authState$.subscribe((loggedIn) => {

      this.user = loggedIn ? this.auth.getUser() : null;

      if (this.user) {
        this.editName = this.user.name;
        this.editEmail = this.user.email;
      } else {
        this.name = '';
        this.email = '';
        this.password = '';
        this.passwordConfirmation = '';
        this.editName = '';
        this.editEmail = '';
        this.currentPassword = '';
        this.newPassword = '';
        this.newPasswordConfirmation = '';
      }

      this.cdr.detectChanges();
    });

    // Detectar si se solicitó iniciar sesión o registrarse
    this.route.queryParamMap.subscribe((params) => {

      const mode = params.get('mode');

      if (mode === 'login' || mode === 'register') {
        this.openAuth(mode);
      }
    });
  }

  openAuth(mode: 'login' | 'register'): void {
    this.mode = mode;
    this.errorMessage = '';
    this.successMessage = '';
  }

  changeMode(value: string | number | undefined): void {

    if (value !== 'login' && value !== 'register') {
      return;
    }

    this.mode = value;
    this.errorMessage = '';
    this.successMessage = '';
  }

  // Inicio de sesión y registro
  submit(): void {

    this.errorMessage = '';
    this.successMessage = '';

    if (!this.email.trim() || !this.password) {
      this.errorMessage = 'Ingresa tu correo y contraseña.';
      return;
    }

    if (this.mode === 'register') {

      if (!this.name.trim()) {
        this.errorMessage = 'Ingresa tu nombre.';
        return;
      }

      if (this.password.length < 8) {
        this.errorMessage =
          'La contraseña debe tener al menos 8 caracteres.';
        return;
      }

      if (this.password !== this.passwordConfirmation) {
        this.errorMessage = 'Las contraseñas no coinciden.';
        return;
      }
    }

    this.loading = true;

    const request =
      this.mode === 'register'
        ? this.auth.register({
            name: this.name.trim(),
            email: this.email.trim(),
            password: this.password,
            password_confirmation: this.passwordConfirmation,
          })
        : this.auth.login({
            email: this.email.trim(),
            password: this.password,
          });

    request.subscribe({

      next: (response) => {

        this.user = response.user;

        this.name = '';
        this.email = '';
        this.password = '';
        this.passwordConfirmation = '';

        this.editName = response.user.name;
        this.editEmail = response.user.email;

        this.successMessage = response.message;
        this.loading = false;

        this.cdr.detectChanges();
      },

      error: (error) => {

        this.loading = false;

        const validationErrors = error.error?.errors;

        if (validationErrors) {
          this.errorMessage = Object.values(validationErrors)
            .flat()
            .join(' ');
        } else {
          this.errorMessage =
            error.error?.message ||
            'No fue posible conectar con PsyShop. Intenta nuevamente.';
        }

        this.cdr.detectChanges();
      },
    });
  }

  // Actualizar datos personales
  updateProfile(): void {

    this.errorMessage = '';
    this.successMessage = '';

    if (!this.editName.trim() || !this.editEmail.trim()) {
      this.errorMessage =
        'Completa tu nombre y correo electrónico.';
      return;
    }

    this.loading = true;

    this.auth.updateProfile({
      name: this.editName.trim(),
      email: this.editEmail.trim(),
    }).subscribe({

      next: (response) => {

        this.user = response.user;
        this.editName = response.user.name;
        this.editEmail = response.user.email;

        this.successMessage = response.message;
        this.loading = false;

        this.cdr.detectChanges();
      },

      error: (error) => {

        this.loading = false;

        const validationErrors = error.error?.errors;

        this.errorMessage = validationErrors
          ? Object.values(validationErrors).flat().join(' ')
          : error.error?.message ||
            'No se pudo actualizar el perfil.';

        this.cdr.detectChanges();
      },
    });
  }

  // Cambiar contraseña
  updatePassword(): void {

    this.errorMessage = '';
    this.successMessage = '';

    if (
      !this.currentPassword ||
      !this.newPassword ||
      !this.newPasswordConfirmation
    ) {
      this.errorMessage =
        'Completa todos los campos de contraseña.';
      return;
    }

    if (this.newPassword.length < 8) {
      this.errorMessage =
        'La nueva contraseña debe tener al menos 8 caracteres.';
      return;
    }

    if (this.newPassword !== this.newPasswordConfirmation) {
      this.errorMessage =
        'Las nuevas contraseñas no coinciden.';
      return;
    }

    this.loading = true;

    this.auth.updatePassword({
      current_password: this.currentPassword,
      password: this.newPassword,
      password_confirmation: this.newPasswordConfirmation,
    }).subscribe({

      next: (response) => {

        this.currentPassword = '';
        this.newPassword = '';
        this.newPasswordConfirmation = '';

        this.successMessage = response.message;
        this.loading = false;

        this.cdr.detectChanges();
      },

      error: (error) => {

        this.loading = false;

        const validationErrors = error.error?.errors;

        this.errorMessage = validationErrors
          ? Object.values(validationErrors).flat().join(' ')
          : error.error?.message ||
            'No se pudo actualizar la contraseña.';

        this.cdr.detectChanges();
      },
    });
  }

  // Cerrar sesión
  logout(): void {

    this.loading = true;
    this.errorMessage = '';
    this.successMessage = '';

    this.auth.logout().subscribe({

      next: (response) => {
        this.clearSessionAndRedirect(
          response?.message || 'Sesión cerrada correctamente.'
        );
      },

      error: (error) => {

        console.warn(
          'Error en servidor al cerrar sesión:',
          error
        );

        this.clearSessionAndRedirect(
          'Sesión cerrada localmente.'
        );
      },
    });
  }

  // Limpiar sesión y regresar al inicio
  private clearSessionAndRedirect(message: string): void {

    this.auth.clearSession();

    this.user = null;

    this.name = '';
    this.email = '';
    this.password = '';
    this.passwordConfirmation = '';

    this.editName = '';
    this.editEmail = '';

    this.currentPassword = '';
    this.newPassword = '';
    this.newPasswordConfirmation = '';

    this.mode = 'login';
    this.loading = false;
    this.errorMessage = '';
    this.successMessage = message;

    this.cdr.detectChanges();

    this.router.navigate(['/tabs/inicio']);
  }
}
