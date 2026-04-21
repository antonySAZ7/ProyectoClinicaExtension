import React from 'react';
import { createBrowserRouter, RouterProvider, Navigate } from "react-router";
import { Root } from "./Root";
import { Home } from "./components/Home";
import { Auth } from "./components/Auth";
import { BookingClient } from "./components/BookingClient";
import { Dashboard } from "./components/Dashboard";
import { PatientsList } from "./components/PatientsList";
import { AppointmentsList } from "./components/AppointmentsList";
import { ConsultationsList } from "./components/ConsultationsList";
import { AppProvider, useAppContext } from "./context/AppContext";

// Protected Route Component
const ProtectedRoute = ({ children, role }: { children: React.ReactNode, role?: 'client' | 'admin' }) => {
  const { user } = useAppContext();
  
  if (!user) {
    return <Navigate to="/login" replace />;
  }
  
  if (role && user.role !== role) {
    return <Navigate to="/" replace />;
  }
  
  return <>{children}</>;
};

const router = createBrowserRouter([
  {
    path: "/",
    Component: Root,
    children: [
      { index: true, Component: Home },
      { path: "login", Component: Auth },
      { path: "register", Component: () => <Auth isRegisterMode={true} /> },
      { path: "admin/login", Component: () => <Auth isAdminLogin={true} /> },
      { 
        path: "booking", 
        Component: () => (
          <ProtectedRoute role="client">
            <BookingClient />
          </ProtectedRoute>
        ) 
      },
      { 
        path: "admin", 
        Component: () => (
          <ProtectedRoute role="admin">
            <Dashboard />
          </ProtectedRoute>
        ),
        children: [
          { index: true, Component: () => <Navigate to="/admin/patients" replace /> },
          { path: "patients", Component: PatientsList },
          { path: "appointments", Component: AppointmentsList },
          { path: "consultas", Component: ConsultationsList },
        ]
      },
    ],
  },
]);

export default function App() {
  return (
    <AppProvider>
      <RouterProvider router={router} />
    </AppProvider>
  );
}
