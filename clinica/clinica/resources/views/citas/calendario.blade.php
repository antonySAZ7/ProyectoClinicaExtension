@extends('layouts.app')

@section('content')
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Calendario de citas</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Vista agenda para consultar citas por fecha y hora.
                    </p>
                </div>

                <a
                    href="{{ route('citas.index') }}"
                    class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Volver a citas
                </a>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
                <div id="calendario-citas"></div>
            </div>
        </div>
    </div>

    {{--
        Popup con estilos inline para evitar conflictos con utilidades CSS
        y asegurar que flote sobre el calendario.
    --}}
    <div id="detalle-cita-popup" style="
        position: fixed;
        z-index: 9999;
        display: none;
        left: 0;
        top: 0;
        width: min(320px, calc(100vw - 24px));
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.20), 0 8px 24px rgba(0,0,0,0.10);
    ">
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px;">
            <div>
                <p style="margin:0; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#6b7280;">
                    Resumen de cita
                </p>
                <h2 id="dc-paciente" style="margin:4px 0 0; font-size:17px; font-weight:700; color:#111827;"></h2>
            </div>
            <button id="dc-cerrar" aria-label="Cerrar" style="
                flex-shrink:0; background:none; border:none; cursor:pointer;
                color:#9ca3af; font-size:16px; line-height:1;
                padding:4px 6px; border-radius:6px;
                transition: background .15s;
            ">X</button>
        </div>

        <div style="margin-top:16px; display:flex; flex-direction:column; gap:10px; font-size:13px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div style="background:#f9fafb; border-radius:8px; padding:10px;">
                    <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af;">Fecha</div>
                    <div id="dc-fecha" style="margin-top:4px; font-weight:600; color:#111827;"></div>
                </div>
                <div style="background:#f9fafb; border-radius:8px; padding:10px;">
                    <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af;">Hora</div>
                    <div id="dc-hora" style="margin-top:4px; font-weight:600; color:#111827;"></div>
                </div>
            </div>
            <div style="background:#f9fafb; border-radius:8px; padding:10px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af;">Estado</div>
                <div id="dc-estado" style="margin-top:4px; font-weight:600; color:#111827;"></div>
            </div>
            <div style="background:#f9fafb; border-radius:8px; padding:10px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af;">Motivo</div>
                <div id="dc-motivo" style="margin-top:4px; color:#374151;"></div>
            </div>
            <div style="background:#f9fafb; border-radius:8px; padding:10px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af;">Observaciones</div>
                <div id="dc-observaciones" style="margin-top:4px; color:#374151;"></div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarioEl = document.getElementById('calendario-citas');
            const popup = document.getElementById('detalle-cita-popup');
            const cerrarBtn = document.getElementById('dc-cerrar');

            // Mover al body para que position: fixed sea relativo al viewport.
            document.body.appendChild(popup);

            function setText(id, value) {
                document.getElementById(id).textContent = value || '-';
            }

            function mostrarPopup(fcEvent, anchorEl) {
                setText('dc-paciente', fcEvent.title);
                setText('dc-fecha', fcEvent.extendedProps.fecha);
                setText('dc-hora', fcEvent.extendedProps.hora);
                setText('dc-estado', fcEvent.extendedProps.estado);
                setText('dc-motivo', fcEvent.extendedProps.motivo);
                setText('dc-observaciones', fcEvent.extendedProps.observaciones);

                // Mostrarlo oculto para medir dimensiones antes de posicionarlo.
                popup.style.visibility = 'hidden';
                popup.style.display = 'block';

                const margin = 12;
                const popupWidth = popup.offsetWidth;
                const popupHeight = popup.offsetHeight;
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;
                const rect = anchorEl.getBoundingClientRect();

                let left = rect.right + margin;
                let top = rect.top;

                if (left + popupWidth + margin > viewportWidth) {
                    left = rect.left - popupWidth - margin;
                }

                if (top + popupHeight + margin > viewportHeight) {
                    top = viewportHeight - popupHeight - margin;
                }

                left = Math.max(margin, left);
                top = Math.max(margin, top);

                popup.style.left = `${left}px`;
                popup.style.top = `${top}px`;
                popup.style.visibility = 'visible';
            }

            function ocultarPopup() {
                popup.style.display = 'none';
            }

            cerrarBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                ocultarPopup();
            });

            document.addEventListener('click', function (event) {
                if (popup.style.display === 'none') {
                    return;
                }

                if (!popup.contains(event.target) && !event.target.closest('.fc-event')) {
                    ocultarPopup();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    ocultarPopup();
                }
            });

            const calendario = new FullCalendar.Calendar(calendarioEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay',
                },
                editable: false,
                selectable: false,
                eventStartEditable: false,
                eventDurationEditable: false,
                displayEventTime: true,
                dayMaxEvents: true,
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Dia',
                },
                events: @json($eventos),
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                },
                eventClick: function (info) {
                    info.jsEvent.preventDefault();
                    info.jsEvent.stopPropagation();
                    mostrarPopup(info.event, info.el);
                },
                noEventsContent: 'No hay citas registradas.',
            });

            calendario.render();
        });
    </script>
@endsection
