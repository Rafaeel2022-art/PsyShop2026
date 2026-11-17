
import { Injectable } from '@angular/core';
import { ToastController } from '@ionic/angular';

@Injectable({
  providedIn: 'root'
})
export class ToastService {

  constructor(
    private toastController: ToastController
  ) {}

  async mostrar(
    mensaje: string,
    color: string = 'primary'
  ): Promise<void> {
    console.log('ToastService: intentando mostrar:', mensaje);

    const toast = await this.toastController.create({
      message: mensaje,
      duration: 5000,
      position: 'middle',
      color: color,
      buttons: [
        {
          text: 'Cerrar',
          role: 'cancel'
        }
      ]
    });

    console.log('Toast creado correctamente');

    await toast.present();

    console.log('Toast presentado');
  }
}
