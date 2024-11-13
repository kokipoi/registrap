import { Component, OnInit, OnDestroy } from '@angular/core';
import { MenuController } from '@ionic/angular';
import { AuthService } from '../../auth.service';
import { Router } from '@angular/router';
import { ToastController } from '@ionic/angular';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-home-profesor',
  templateUrl: './home-profesor.page.html',
  styleUrls: ['./home-profesor.page.scss'],
})
export class HomeProfesorPage implements OnInit, OnDestroy {
  username: string = 'Profesor';  // Valor por defecto
  private userSubscription: Subscription = new Subscription();
  qrCodeValue: string | null = null;  // Valor del QR generado

  constructor(
    private authService: AuthService,
    private router: Router,
    private toastController: ToastController,
    private menuController: MenuController
  ) {}

  ngOnInit() {
    this.userSubscription = this.authService.currentUser$.subscribe(user => {
      if (user) {
        this.username = user.nombre || 'Profesor';
      } else {
        this.router.navigate(['/login']);
      }
    });

    const user = this.authService.getUser();
    if (!user) {
      this.router.navigate(['/login']);
    }
  }

  ngOnDestroy() {
    this.userSubscription.unsubscribe();
  }

  // Función para generar el código QR
  generateQrCode() {
    // Genera un valor para el QR, puede ser dinámico basado en la clase o usuario
    this.qrCodeValue = 'Clase1234';  // Ejemplo de valor, puedes ajustarlo según tus necesidades
  }

  async logout() {
    this.authService.logout();
    await this.presentToast('Has cerrado sesión', 'bottom', 3000, 'success');
    this.router.navigate(['/login']);
  }

  async presentToast(message: string, position: 'top' | 'bottom', duration: number, color: 'success' | 'danger') {
    const toast = await this.toastController.create({
      message,
      duration,
      position,
      color,
    });
    toast.present();
  }

  async openProfileMenu() {
    await this.menuController.open('end');
  }
}
