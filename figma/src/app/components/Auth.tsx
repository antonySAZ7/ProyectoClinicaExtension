import React, { useState } from 'react';
import { useNavigate } from 'react-router';
import { AnimatePresence, motion as Motion } from 'motion/react';
import { toast } from 'sonner';

import { Button, Card, Input } from './ui/shared';
import { useAppContext } from '../context/AppContext';
import { siteContent } from '../config/siteContent';

interface AuthProps {
  isRegisterMode?: boolean;
  isAdminLogin?: boolean;
}

export const Auth = ({ isRegisterMode = false, isAdminLogin = false }: AuthProps) => {
  const [isLogin, setIsLogin] = useState(!isRegisterMode);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [name, setName] = useState('');
  const [errors, setErrors] = useState<{ [key: string]: string }>({});

  const { login } = useAppContext();
  const navigate = useNavigate();

  const validate = () => {
    const newErrors: { [key: string]: string } = {};
    if (isAdminLogin) return true;

    if (!email) newErrors.email = 'El correo es obligatorio';
    if (!password) newErrors.password = 'La contraseña es obligatoria';
    if (!isLogin && !name) newErrors.name = 'El nombre es obligatorio';

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!validate()) return;

    if (isAdminLogin) {
      login('admin', siteContent.brand.adminEmail, `Equipo ${siteContent.brand.name}`);
      navigate('/admin');
      toast.success('Sesión administrativa iniciada');
      return;
    }

    if (isLogin) {
      if (password.length < 6) {
        setErrors({ general: 'Correo o contraseña incorrectos' });
        toast.error('Correo o contraseña incorrectos');
      } else {
        login('client', email, 'Paciente DENS32');
        navigate('/booking');
        toast.success('Bienvenido de nuevo');
      }
    } else {
      login('client', email, name);
      navigate('/booking');
      toast.success('Cuenta creada exitosamente');
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center bg-[var(--brand-soft)] px-4">
      <Card className="w-full max-w-[420px] p-8">
        <div className="mb-8 text-center">
          <button
            onClick={() => navigate('/')}
            className="mb-2 cursor-pointer border-none bg-transparent font-display text-5xl leading-none text-[var(--brand-primary)]"
          >
            {siteContent.brand.name}
          </button>
          <p className="text-sm uppercase tracking-[0.28em] text-[var(--brand-muted)]">
            {siteContent.brand.subtitle}
          </p>
          <p className="mt-4 text-[var(--brand-muted)]">
            {isAdminLogin
              ? 'Panel administrativo'
              : isLogin
                ? 'Bienvenido de nuevo'
                : 'Crea tu cuenta'}
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {!isAdminLogin && (
            <>
              <AnimatePresence mode="wait">
                {!isLogin && (
                  <Motion.div
                    key="name-input"
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: 'auto' }}
                    exit={{ opacity: 0, height: 0 }}
                  >
                    <Input
                      label="Nombre completo *"
                      placeholder="Tu nombre"
                      value={name}
                      onChange={(e) => setName(e.target.value)}
                      error={errors.name}
                    />
                  </Motion.div>
                )}
              </AnimatePresence>

              <Input
                label="Correo electrónico *"
                placeholder="correo@ejemplo.com"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                error={errors.email}
              />

              <Input
                label="Contraseña *"
                type="password"
                placeholder="••••••••"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                error={errors.password}
              />
            </>
          )}

          {isAdminLogin && (
            <div className="rounded-2xl border border-[var(--brand-border)] bg-white px-4 py-5 text-center">
              <p className="text-[var(--brand-primary)]">
                Acceso directo para el personal médico de {siteContent.brand.name}.
              </p>
            </div>
          )}

          {errors.general && (
            <p className="text-center text-sm text-red-500">{errors.general}</p>
          )}

          <Button fullWidth type="submit">
            {isLogin ? 'Iniciar sesión' : 'Crear cuenta'}
          </Button>

          {!isAdminLogin && (
            <p className="text-center text-sm text-[var(--brand-muted)]">
              {isLogin ? '¿No tienes cuenta? ' : '¿Ya tienes cuenta? '}
              <button
                type="button"
                onClick={() => setIsLogin(!isLogin)}
                className="cursor-pointer border-none bg-transparent font-semibold text-[var(--brand-primary)] hover:underline"
              >
                {isLogin ? 'Crea tu cuenta' : 'Inicia sesión'}
              </button>
            </p>
          )}

          {isLogin && !isAdminLogin && (
            <div className="text-center">
              <button
                type="button"
                onClick={() => navigate('/admin/login')}
                className="cursor-pointer border-none bg-transparent text-xs text-[var(--brand-muted)] hover:underline"
              >
                Acceso Personal Médico
              </button>
            </div>
          )}
        </form>
      </Card>
    </div>
  );
};
