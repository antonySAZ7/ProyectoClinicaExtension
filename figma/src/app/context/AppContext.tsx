import React, { createContext, useContext, useState } from 'react';

export type Role = 'client' | 'admin' | null;

/* ============================
   APPOINTMENTS
============================ */

export interface Appointment {
  id: string;
  patientName: string;
  type: string;
  date: string;
  time: string;
  status: 'pending' | 'confirmed' | 'cancelled';
  notes?: string;
}

/* ============================
   PATIENTS
============================ */

export interface Patient {
  id: string;
  name: string;
  dpi: string;
  birthDate: string;
  phone: string;
  email: string;
  address: string;
  allergies?: string;
  conditions?: string;
  medications?: string;
}

/* ============================
   CONSULTATIONS
============================ */

export interface Treatment {
  id: string;
  tooth: string;
  diagnosis: string;
  procedure: string;
  price: string;
}

export interface Consultation {
  id: string;
  patientId: string;
  date: string;
  reason: string;
  weight?: string;
  height?: string;
  bloodPressure?: string;
  heartRate?: string;
  respiratoryRate?: string;
  observations?: string;
  treatments: Treatment[];
}

/* ============================
   CONTEXT TYPE
============================ */

interface AppContextType {
  user: { role: Role; name?: string; email?: string } | null;
  login: (role: Role, email: string, name?: string) => void;
  logout: () => void;

  appointments: Appointment[];
  addAppointment: (appointment: Omit<Appointment, 'id'>) => void;
  updateAppointmentStatus: (id: string, status: Appointment['status']) => void;

  patients: Patient[];
  addPatient: (patient: Omit<Patient, 'id'>) => void;

  consultations: Consultation[];
  addConsultation: (consultation: Omit<Consultation, 'id'>) => void;
}

/* ============================
   CONTEXT
============================ */

const AppContext = createContext<AppContextType | undefined>(undefined);

export function AppProvider({ children }: { children: React.ReactNode }) {

  const [user, setUser] = useState<{ role: Role; name?: string; email?: string } | null>(null);

  const [appointments, setAppointments] = useState<Appointment[]>([
    { id: '1', patientName: 'Juan Pérez', type: 'Limpieza', date: '2026-02-21', time: '09:00', status: 'confirmed' },
    { id: '2', patientName: 'María García', type: 'Ortodoncia', date: '2026-02-21', time: '10:30', status: 'pending' },
  ]);

  const [patients, setPatients] = useState<Patient[]>([
    { id: '1', name: 'Juan Pérez', dpi: '123456789', birthDate: '1990-05-15', phone: '5555-1234', email: 'juan@mail.com', address: 'Calle 1, Ciudad' },
  ]);

  const [consultations, setConsultations] = useState<Consultation[]>([]);

  /* ============================
     AUTH
  ============================ */

  const login = (role: Role, email: string, name?: string) => {
    setUser({ role, email, name: name || (role === 'admin' ? 'Doc' : 'Paciente') });
  };

  const logout = () => {
    setUser(null);
  };

  /* ============================
     APPOINTMENTS
  ============================ */

  const addAppointment = (appointment: Omit<Appointment, 'id'>) => {
    const newAppointment = {
      ...appointment,
      id: Math.random().toString(36).substring(2, 9)
    };
    setAppointments(prev => [...prev, newAppointment]);
  };

  const updateAppointmentStatus = (id: string, status: Appointment['status']) => {
    setAppointments(prev =>
      prev.map(app => app.id === id ? { ...app, status } : app)
    );
  };

  /* ============================
     PATIENTS
  ============================ */

  const addPatient = (patient: Omit<Patient, 'id'>) => {
    const newPatient = {
      ...patient,
      id: Math.random().toString(36).substring(2, 9)
    };
    setPatients(prev => [...prev, newPatient]);
  };

  /* ============================
     CONSULTATIONS
  ============================ */

  const addConsultation = (consultation: Omit<Consultation, 'id'>) => {
    const newConsultation = {
      ...consultation,
      id: Math.random().toString(36).substring(2, 9)
    };
    setConsultations(prev => [...prev, newConsultation]);
  };

  return (
    <AppContext.Provider
      value={{
        user,
        login,
        logout,
        appointments,
        addAppointment,
        updateAppointmentStatus,
        patients,
        addPatient,
        consultations,
        addConsultation
      }}
    >
      {children}
    </AppContext.Provider>
  );
}

export function useAppContext() {
  const context = useContext(AppContext);
  if (context === undefined) {
    throw new Error('useAppContext must be used within an AppProvider');
  }
  return context;
}