export interface User {
    id: number;
    name: string;
    email?: string;
    photo: string | null;
}

export interface Comment {
    id: number;
    body: string;
    created_at_human: string;
    user: User | null;
    is_owner: boolean;
    likes_count: number;        // 🆕
    has_liked: boolean;         // 🆕
    replies?: Comment[];
}

export interface Publication {
    id: number;
    text: string;
    images: string[];
    video: string | null;
    audio: string | null;
    created_at: string;
    created_at_human: string;
    user: User | null;
    is_owner: boolean;          // 🆕
    views_count: number;
    likes_count: number;
    comments_count: number;
    shares_count: number;
    has_liked: boolean;
    comments: Comment[];
}



export interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    image: string | null;
    category: {
        id: number;
        name: string;
    } | null;
}

export interface PointDeVente {
    id: number;
    name: string;
    address: string;
    phone: string;
    lat: number;
    lng: number;
    hours: string;
    isOpen?: boolean;
    distance?: number;
}

export interface PageProps {
    auth: {
        user: User | null;
    };
    flash?: {
        success?: string;
        error?: string;
    };
    [key: string]: unknown;
}

export interface Category {
    id: number;
    name: string;
    products_count?: number;
}

export interface ShopProduct {
    id: number;
    name: string;
    description: string;
    price: number;
    image: string | null;
    images: string[];
    category: {
        id: number;
        name: string;
    } | null;
    created_at: string;
}

export interface CartItem {
    id: number;
    name: string;
    price: number;
    image: string | null;
    quantity: number;
}

export interface Cart {
    [key: string]: CartItem;
}

export interface PaginatedProducts {
    data: ShopProduct[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

export interface ShopFilters {
    search: string;
    category: string;
    price_min: string;
    price_max: string;
    sort: string;
}

export interface InvestStats {
    total_invested: number;
    total_shares: number;
    total_investors: number;
    countries_count: number;
}

export interface CurrentRound {
    id: number;
    name: string;
    amount: number;
    begin: string | null;
    end: string | null;
    days_remaining: number | null;
}

export interface TopInvestor {
    id: number;
    ref: string;
    name: string;
    photo: string | null;
    total_invested: number;
    total_shares: number;
}

export interface UserInvestment {
    total: number;
    shares: number;
}

export interface PaymentHistoryItem {
    id: number;
    created_at: string;
    created_at_formatted: string;
    created_at_human: string;
    share: number;
    round_name: string;
    invested_amount: number;
    user: {
        name: string;
        ref: string;
        photo: string | null;
    };
}

export interface MonthlyStats {
    current: number;
    last: number;
    growth: number;
}