import aboutTeam from '@/assets/images/about-team.jpg';
import heroPrimary from '@/assets/images/hero-primary.jpg';
import heroSecondary from '@/assets/images/hero-secondary.jpg';
import missionPhoto from '@/assets/images/mision.jpg';
import visionPhoto from '@/assets/images/vision.jpg';

export const siteContent = {
  brand: {
    name: 'DENS32',
    subtitle: 'Clínica Dental',
    legalName: 'DENS32 Clínica Dental',
    tagline: 'Comprometidos con tu salud desde ya',
    founders: ['Katherin Ruiz', 'Marco Pinelo'],
    year: 2026,
    phoneDisplay: '(502) 3819-8918',
    phoneHref: 'tel:+50238198918',
    instagramDisplay: '@Dens.32_',
    instagramHref: 'https://www.instagram.com/dens.32_/',
    emailDisplay: 'clinicadental.dens32@gmail.com',
    emailHref: 'mailto:clinicadental.dens32@gmail.com',
    adminEmail: 'admin@dens32.com',
  },
  about: {
    eyebrow: 'Acerca de nosotros',
    title: 'Odontología con propósito y calidez humana',
    paragraphs: [
      'Somos Katherin Ruiz y Marco Pinelo, estudiantes de último año de odontología en la Universidad San Carlos de Guatemala. Desde el inicio de nuestra formación, nos hemos apasionado por la salud bucal y su impacto en la calidad de vida de las personas. Nuestra clínica dental, DENS32, nace con el propósito de brindar atención odontológica de calidad y con calidez humana.',
      'Vivir en Guatemala nos ha sensibilizado sobre las necesidades de nuestra comunidad. Nos afecta ver cómo la salud no siempre es una prioridad y cómo muchas personas no tienen acceso a cuidados dentales básicos. Queremos ser parte del cambio, ser la mano amiga que acompaña a quienes más lo necesitan y contribuir a una Guatemala más saludable y feliz.',
    ],
  },
  objectives: [
    {
      title: 'Brindar atención odontológica de calidad',
      description:
        'Ofrecer servicios dentales profesionales, accesibles y de excelencia, utilizando las últimas tecnologías y técnicas en odontología.',
    },
    {
      title: 'Promover la salud bucal en comunidades vulnerables',
      description:
        'Realizar jornadas de salud dental en áreas de difícil acceso y colaborar con asociaciones y fundaciones que trabajan en pro de la salud comunitaria.',
    },
    {
      title: 'Educar sobre la importancia de la salud bucal',
      description:
        'Implementar programas de educación y prevención en escuelas, comunidades y centros de trabajo para fomentar hábitos saludables desde temprana edad.',
    },
    {
      title: 'Desarrollar una práctica empática y humanitaria',
      description:
        'Construir relaciones basadas en el respeto, la empatía y el carisma, poniendo siempre al paciente en el centro de nuestra atención.',
    },
  ],
  mission: {
    title: 'Nuestra Misión',
    description:
      'Mejorar la salud bucal en Guatemala, ofreciendo servicios odontológicos accesibles y preventivos. Con empatía y dedicación, reducimos la desigualdad en el acceso a la salud dental, colaborando con asociaciones para llevar sonrisas y bienestar a todas las comunidades.',
  },
  vision: {
    title: 'Nuestra Visión',
    description:
      'Ser un referente en odontología comunitaria en Guatemala, reconocidos por nuestra empatía, profesionalismo y compromiso con la salud bucal de todos, especialmente los más vulnerables. Aspiramos a un país donde todos tengan acceso a cuidados dentales de calidad y puedan sonreír con confianza.',
  },
  photos: {
    heroPrimary,
    heroSecondary,
    aboutTeam,
    mission: missionPhoto,
    vision: visionPhoto,
    contact: heroSecondary,
    objectives: 'https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1400',
  },
  framing: {
    heroPrimary: '52% 44%',
    heroSecondary: '52% 42%',
    aboutTeam: '50% 18%',
    mission: '50% 32%',
    vision: '50% 30%',
    contact: '52% 42%',
  },
} as const;
