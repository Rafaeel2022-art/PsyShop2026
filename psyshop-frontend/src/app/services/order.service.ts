import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface OrderRequest {
  customer_name: string;
  customer_email: string;
  customer_phone: string;
  delivery_address: string;
  items: {
    game_id: number;
    quantity: number;
  }[];
}

export interface OrderItem {
  id: number;
  game_id: number;
  quantity: number;
  unit_price: string | number;
  subtotal: string | number;
  game?: {
    id: number;
    title: string;
    image_url?: string | null;
    platform?: string;
  } | null;
}

export interface OrderStatusHistory {
  id: number;
  order_id: number;
  status: string;
  description: string | null;
  created_at: string;
  updated_at: string;
}

export interface CustomerOrder {
  id: number;
  customer_name: string;
  customer_email?: string;
  customer_phone?: string;
  delivery_address: string;
  subtotal: string | number;
  total: string | number;
  status: string;
  created_at: string;
  items: OrderItem[];
  status_history: OrderStatusHistory[];
}

export interface OrderResponse {
  message: string;
  order: CustomerOrder;
}

export interface MyOrdersResponse {
  current_page: number;
  data: CustomerOrder[];
  last_page: number;
  total: number;
}

@Injectable({
  providedIn: 'root'
})
export class OrderService {
  private http = inject(HttpClient);
  private readonly apiUrl = 'http://127.0.0.1:8000/api';

  createOrder(data: OrderRequest): Observable<OrderResponse> {
    return this.http.post<OrderResponse>(
      `${this.apiUrl}/orders`,
      data
    );
  }

  getMyOrders(page: number = 1): Observable<MyOrdersResponse> {
    return this.http.get<MyOrdersResponse>(
      `${this.apiUrl}/my-orders`,
      { params: { page: page.toString() } }
    );
  }
}
