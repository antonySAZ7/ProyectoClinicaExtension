import React, { useState } from 'react';
import { DayPicker } from 'react-day-picker';
import { format, startOfToday } from 'date-fns';
import { es } from 'date-fns/locale';
import { Button, Input, Card } from './ui/shared';
import { useAppContext } from '../context/AppContext';
import { toast } from 'sonner';
import { Calendar as CalendarIcon, Clock, ChevronRight, History } from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';

// Pre-occupied dates for simulation
const occupiedDates = [
  new Date(2026, 1, 25), // Feb 25 2026
  new Date(2026, 1, 26),
];

export const BookingClient = () => {
  const { addAppointment, appointments, user } = useAppContext();
  const [selectedDate, setSelectedDate] = useState<Date | undefined>(new Date());
  const [selectedTime, setSelectedTime] = useState('');
  const [service, setService] = useState('Limpieza');
  const [notes, setNotes] = useState('');
  const [showHistory, setShowHistory] = useState(false);

  const times = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00'];

  // Check if a slot is taken
  const isTimeOccupied = (time: string) => {
    if (!selectedDate) return false;
    const dateStr = format(selectedDate, 'yyyy-MM-dd');
    return appointments.some(app => app.date === dateStr && app.time === time && app.status !== 'cancelled');
  };

  const handleBooking = () => {
    if (!selectedDate || !selectedTime) {
      toast.error('Por favor selecciona fecha y hora');
      return;
    }

    if (isTimeOccupied(selectedTime)) {
      toast.error('Este horario ya no está disponible.');
      return;
    }

    addAppointment({
      patientName: user?.name || 'Paciente',
      type: service,
      date: format(selectedDate, 'yyyy-MM-dd'),
      time: selectedTime,
      status: 'pending',
      notes
    });

    toast.success('Cita registrada exitosamente');
    setSelectedTime('');
    setNotes('');
  };

  return (
    <div className="mx-auto max-w-7xl px-6 py-12 pt-24">
      <div className="flex justify-between items-center mb-8">
        <div>
          <h1 className="font-display text-5xl leading-none text-[var(--brand-primary)]">Reserva tu cita</h1>
          <p className="mt-2 text-[var(--brand-muted)]">Selecciona el mejor horario para tu consulta</p>
        </div>
        <Button variant="outline" className="flex items-center gap-2" onClick={() => setShowHistory(true)}>
          <History size={18} />
          Ver mis citas
        </Button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-10 gap-8">
        {/* Left Column: Calendar */}
        <Card className="lg:col-span-7 p-8">
          <div className="flex flex-col md:flex-row gap-12">
            <div className="flex-1">
              <h3 className="text-lg font-semibold mb-6 flex items-center gap-2">
                <CalendarIcon size={20} className="text-[var(--brand-primary)]" />
                1. Selecciona el día
              </h3>
              <div className="calendar-container border rounded-2xl p-4 bg-white shadow-sm flex justify-center">
                <DayPicker
                  mode="single"
                  selected={selectedDate}
                  onSelect={setSelectedDate}
                  locale={es}
                  disabled={{ before: startOfToday() }}
                  modifiers={{
                    occupied: occupiedDates,
                  }}
                  modifiersStyles={{
                    occupied: { color: '#EF4444', fontWeight: 'bold' },
                    selected: { backgroundColor: '#35342f', color: 'white' }
                  }}
                  className="mx-auto"
                />
              </div>
              <div className="mt-6 flex gap-4 text-xs">
                <div className="flex items-center gap-1.5">
                  <div className="w-3 h-3 rounded-full bg-[#EF4444]" />
                  <span>Ocupado</span>
                </div>
                <div className="flex items-center gap-1.5">
                  <div className="w-3 h-3 rounded-full bg-[#22C55E]" />
                  <span>Disponible</span>
                </div>
                <div className="flex items-center gap-1.5">
                  <div className="w-3 h-3 rounded-full bg-[var(--brand-primary)]" />
                  <span>Seleccionado</span>
                </div>
              </div>
            </div>

            <div className="flex-1">
              <h3 className="text-lg font-semibold mb-6 flex items-center gap-2">
                <Clock size={20} className="text-[var(--brand-primary)]" />
                2. Selecciona la hora
              </h3>
              <div className="grid grid-cols-2 gap-3">
                {times.map((time) => {
                  const occupied = isTimeOccupied(time);
                  return (
                    <button
                      key={time}
                      disabled={occupied}
                      onClick={() => setSelectedTime(time)}
                      className={`
                        p-3 rounded-xl border text-sm font-medium transition-all
                        ${occupied ? 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed line-through' : 
                          selectedTime === time ? 'bg-[var(--brand-primary)] text-white border-[var(--brand-primary)] shadow-md' : 
                          'hover:border-[var(--brand-primary)] hover:text-[var(--brand-primary)] border-[var(--brand-border)]'}
                      `}
                    >
                      {time}
                    </button>
                  );
                })}
              </div>
              {selectedTime && !isTimeOccupied(selectedTime) && (
                <p className="mt-4 text-sm text-green-600 font-medium">Horario disponible para el {format(selectedDate!, 'dd/MM/yyyy')}</p>
              )}
            </div>
          </div>
        </Card>

        {/* Right Column: Form */}
        <Card className="lg:col-span-3 p-8 flex flex-col justify-between">
          <div className="space-y-6">
              <h3 className="text-lg font-semibold flex items-center gap-2">
                <ChevronRight size={20} className="text-[var(--brand-primary)]" />
                3. Detalles
              </h3>

            <div className="space-y-4">
              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-medium text-gray-500">Tipo de cita</label>
                <select 
                  className="w-full rounded-[14px] border border-[var(--brand-border)] bg-white px-4 py-3 outline-none focus:border-[var(--brand-primary)]"
                  value={service}
                  onChange={(e) => setService(e.target.value)}
                >
                  <option>Limpieza</option>
                  <option>Ortodoncia</option>
                  <option>Extracción</option>
                  <option>Evaluación</option>
                </select>
              </div>

              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-medium text-gray-500">Notas adicionales</label>
                <textarea 
                  className="min-h-[100px] w-full rounded-[14px] border border-[var(--brand-border)] bg-white px-4 py-3 outline-none focus:border-[var(--brand-primary)]"
                  placeholder="Ej. Sensibilidad dental..."
                  value={notes}
                  onChange={(e) => setNotes(e.target.value)}
                />
              </div>

              <div className="space-y-2 rounded-xl border border-[var(--brand-border)] bg-[var(--brand-soft)] p-4">
                <div className="flex justify-between text-sm">
                  <span className="text-gray-500">Fecha:</span>
                  <span className="font-semibold">{selectedDate ? format(selectedDate, 'dd/MM/yyyy') : '-'}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-gray-500">Hora:</span>
                  <span className="font-semibold">{selectedTime || '-'}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-gray-500">Servicio:</span>
                  <span className="font-semibold">{service}</span>
                </div>
              </div>
            </div>
          </div>

          <Button 
            fullWidth 
            className="mt-8" 
            onClick={handleBooking}
            disabled={!selectedDate || !selectedTime}
          >
            Confirmar cita
          </Button>
        </Card>
      </div>

      {/* History Modal */}
      <AnimatePresence>
        {showHistory && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <motion.div 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="absolute inset-0 bg-black/40"
              onClick={() => setShowHistory(false)}
            />
            <motion.div
              initial={{ opacity: 0, scale: 0.95, y: 20 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.95, y: 20 }}
              className="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden"
            >
              <div className="p-6 border-b flex justify-between items-center">
                <h2 className="text-xl font-bold">Mis Citas Anteriores</h2>
                <button onClick={() => setShowHistory(false)} className="text-gray-400 hover:text-gray-600">✕</button>
              </div>
              <div className="p-6 max-h-[60vh] overflow-y-auto">
                {appointments.filter(a => a.patientName === user?.name).length > 0 ? (
                  <div className="space-y-4">
                    {appointments.filter(a => a.patientName === user?.name).map((app) => (
                      <div key={app.id} className="p-4 rounded-xl border border-gray-100 flex justify-between items-center">
                        <div>
                          <p className="font-bold text-[var(--brand-primary)]">{app.type}</p>
                          <p className="text-sm text-gray-500">{format(new Date(app.date), 'dd MMMM, yyyy', { locale: es })} • {app.time}</p>
                        </div>
                        <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
                          app.status === 'confirmed' ? 'bg-green-100 text-green-600' : 
                          app.status === 'pending' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600'
                        }`}>
                          {app.status === 'confirmed' ? 'Confirmada' : app.status === 'pending' ? 'Pendiente' : 'Cancelada'}
                        </span>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-center py-12 text-gray-500">No tienes citas registradas.</p>
                )}
              </div>
              <div className="bg-[var(--brand-soft)] p-6 text-right">
                <Button onClick={() => setShowHistory(false)}>Cerrar</Button>
              </div>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
    </div>
  );
};
