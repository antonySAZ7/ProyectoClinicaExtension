<?php

/*
|--------------------------------------------------------------------------
| Site Content (Landing DENS32)
|--------------------------------------------------------------------------
|
| Espejo de figma/src/app/config/siteContent.ts. Fuente única para textos,
| datos de marca, contactos e imágenes del landing público. Editar aquí
| para reflejar cambios en la vista sin tocar la maqueta.
|
*/

return [

    'brand' => [
        'name'              => 'DENS32',
        'subtitle'          => 'Clínica Dental',
        'legal_name'        => 'DENS32 Clínica Dental',
        'tagline'           => 'Comprometidos con tu salud desde ya',
        'founders'          => ['Katherin Ruiz', 'Marco Pinelo'],
        'year'              => 2026,
        'phone_display'     => '(502) 3819-8918',
        'phone_href'        => 'tel:+50238198918',
        'instagram_display' => '@Dens.32_',
        'instagram_href'    => 'https://www.instagram.com/dens.32_/',
        'email_display'     => 'clinicadental.dens32@gmail.com',
        'email_href'        => 'mailto:clinicadental.dens32@gmail.com',
        'admin_email'       => 'admin@dens32.com',
    ],

    'hero' => [
        'description' => 'Atención odontológica accesible, preventiva y centrada en las personas. Un proyecto humano que busca transformar sonrisas en Guatemala.',
    ],

    'about' => [
        'eyebrow'    => 'Acerca de nosotros',
        'title'      => 'Odontología con propósito y calidez humana',
        'paragraphs' => [
            'Somos Katherin Ruiz y Marco Pinelo, estudiantes de último año de odontología en la Universidad San Carlos de Guatemala. Desde el inicio de nuestra formación, nos hemos apasionado por la salud bucal y su impacto en la calidad de vida de las personas. Nuestra clínica dental, DENS32, nace con el propósito de brindar atención odontológica de calidad y con calidez humana.',
            'Vivir en Guatemala nos ha sensibilizado sobre las necesidades de nuestra comunidad. Nos afecta ver cómo la salud no siempre es una prioridad y cómo muchas personas no tienen acceso a cuidados dentales básicos. Queremos ser parte del cambio, ser la mano amiga que acompaña a quienes más lo necesitan y contribuir a una Guatemala más saludable y feliz.',
        ],
        'highlights' => [
            [
                'icon'        => 'heart-handshake',
                'title'       => 'Atención con empatía',
                'description' => 'Buscamos acompañar a cada paciente con calidez humana y cercanía.',
            ],
            [
                'icon'        => 'sparkles',
                'title'       => 'Propósito comunitario',
                'description' => 'Queremos llevar salud bucal de calidad a quienes más la necesitan.',
            ],
        ],
        'team_caption' => 'Equipo fundador',
        'side_card'    => [
            'eyebrow'     => 'DENS32',
            'title'       => 'Un proyecto creado por Katherin Ruiz y Marco Pinelo.',
            'description' => 'Combinamos formación académica, sensibilidad social y vocación de servicio para ofrecer una atención dental cercana, ética y comprometida con la comunidad.',
        ],
    ],

    'objectives' => [
        'eyebrow' => 'Nuestros objetivos',
        'title'   => 'Lo que queremos construir con DENS32',
        'items'   => [
            [
                'title'       => 'Brindar atención odontológica de calidad',
                'description' => 'Ofrecer servicios dentales profesionales, accesibles y de excelencia, utilizando las últimas tecnologías y técnicas en odontología.',
            ],
            [
                'title'       => 'Promover la salud bucal en comunidades vulnerables',
                'description' => 'Realizar jornadas de salud dental en áreas de difícil acceso y colaborar con asociaciones y fundaciones que trabajan en pro de la salud comunitaria.',
            ],
            [
                'title'       => 'Educar sobre la importancia de la salud bucal',
                'description' => 'Implementar programas de educación y prevención en escuelas, comunidades y centros de trabajo para fomentar hábitos saludables desde temprana edad.',
            ],
            [
                'title'       => 'Desarrollar una práctica empática y humanitaria',
                'description' => 'Construir relaciones basadas en el respeto, la empatía y el carisma, poniendo siempre al paciente en el centro de nuestra atención.',
            ],
        ],
    ],

    'mission' => [
        'eyebrow'     => 'Nuestra misión',
        'title'       => 'Nuestra Misión',
        'description' => 'Mejorar la salud bucal en Guatemala, ofreciendo servicios odontológicos accesibles y preventivos. Con empatía y dedicación, reducimos la desigualdad en el acceso a la salud dental, colaborando con asociaciones para llevar sonrisas y bienestar a todas las comunidades.',
    ],

    'vision' => [
        'eyebrow'     => 'Nuestra visión',
        'title'       => 'Nuestra Visión',
        'description' => 'Ser un referente en odontología comunitaria en Guatemala, reconocidos por nuestra empatía, profesionalismo y compromiso con la salud bucal de todos, especialmente los más vulnerables. Aspiramos a un país donde todos tengan acceso a cuidados dentales de calidad y puedan sonreír con confianza.',
    ],

    'contact' => [
        'eyebrow'     => 'Contacto',
        'title'       => 'Vivimos para servirte',
        'description' => 'Estamos listos para recibirte con un espacio cómodo, moderno y pensado para brindarte una experiencia segura desde tu primera visita.',
        'overlay_text' => 'Estamos listos para recibirte con un espacio cómodo, moderno y pensado para brindarte una experiencia segura desde tu primera visita.',
    ],

    'photos' => [
        'hero_primary'   => 'images/landing/hero-primary.jpg',
        'hero_secondary' => 'images/landing/hero-secondary.jpg',
        'about_team'     => 'images/landing/about-team.jpg',
        'mission'        => 'images/landing/mision.jpg',
        'vision'         => 'images/landing/vision.jpg',
        'contact'        => 'images/landing/hero-secondary.jpg',
    ],

    'framing' => [
        'hero_primary'   => '52% 44%',
        'hero_secondary' => '52% 42%',
        'about_team'     => '50% 18%',
        'mission'        => '50% 32%',
        'vision'         => '50% 30%',
        'contact'        => '52% 42%',
    ],

    'seo' => [
        'title'       => 'DENS32 - Clínica Dental | Comprometidos con tu salud',
        'description' => 'Atención odontológica accesible, preventiva y centrada en las personas en Guatemala. Un proyecto humano de Katherin Ruiz y Marco Pinelo.',
        'og_image'    => 'images/landing/hero-primary.jpg',
    ],

];
