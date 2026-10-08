export const pageNames = ['home', 'about', 'services', 'contact'] as const;
export const welcomeMessageContentMaxLength = 255;

export type PageName = (typeof pageNames)[number];

export interface WelcomeMessage {
    page: PageName;
    content: string;
}

export type ContentState = 'loading' | 'empty' | 'error' | 'ready';
