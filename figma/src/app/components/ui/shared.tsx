import React from 'react';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline' | 'success' | 'ghost';
  fullWidth?: boolean;
}

export const Button = ({ 
  className, 
  variant = 'primary', 
  fullWidth,
  children, 
  ...props 
}: ButtonProps) => {
  const variants = {
    primary: 'bg-[var(--brand-primary)] text-[var(--brand-contrast)] hover:bg-[var(--brand-primary-strong)]',
    secondary: 'bg-[var(--secondary)] text-[var(--brand-primary)] hover:bg-[var(--brand-border)]',
    outline: 'border-2 border-[var(--brand-border)] text-[var(--brand-primary)] hover:border-[var(--brand-primary)] hover:bg-[var(--brand-primary)] hover:text-[var(--brand-contrast)]',
    success: 'bg-[#22C55E] text-white hover:bg-[#1ea34d]',
    ghost: 'text-[var(--brand-muted)] hover:text-[var(--brand-primary)] bg-transparent'
  };

  return (
    <button
      className={cn(
        'px-6 py-3 rounded-[14px] font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer',
        variants[variant],
        fullWidth ? 'w-full' : '',
        className
      )}
      {...props}
    >
      {children}
    </button>
  );
};

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
}

export const Input = ({ label, error, className, ...props }: InputProps) => {
  return (
    <div className="flex flex-col gap-1.5 w-full">
      {label && <label className="text-sm font-medium text-gray-500">{label}</label>}
      <input
        className={cn(
        'w-full px-4 py-3 rounded-[14px] border border-gray-200 outline-none transition-all',
          'border-[var(--brand-border)] bg-white text-[var(--brand-primary)] placeholder:text-[var(--brand-muted)]',
          'focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-black/5',
          error ? 'border-red-500' : '',
          className
        )}
        {...props}
      />
      {error && <span className="text-xs text-red-500 mt-1">{error}</span>}
    </div>
  );
};

export const Card = ({ children, className }: { children: React.ReactNode; className?: string }) => {
  return (
    <div className={cn('bg-[var(--brand-surface)] p-6 rounded-[16px] shadow-[0_20px_50px_rgba(28,28,26,0.06)] border border-[var(--brand-border)]', className)}>
      {children}
    </div>
  );
};
