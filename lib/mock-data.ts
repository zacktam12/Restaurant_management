export type Restaurant = {
  id: string
  name: string
  description: string
  cuisine: string
  address: string
  phone: string
  priceRange: "$" | "$$" | "$$$" | "$$$$"
  rating: number
  image: string
  seatingCapacity: number
}

export type MenuItem = {
  id: string
  restaurantId: string
  name: string
  description: string
  price: number
  category: "appetizer" | "main" | "dessert" | "beverage"
  image: string
  available: boolean
}

export type Reservation = {
  id: string
  restaurantId: string
  customerName: string
  customerEmail: string
  customerPhone: string
  date: string
  time: string
  guests: number
  status: "pending" | "confirmed" | "cancelled" | "completed"
  specialRequests?: string
}

export type ExternalService = {
  id: string
  type: "tour" | "hotel" | "taxi"
  name: string
  description: string
  price: number
  image: string
  rating: number
  available: boolean
}

export type Review = {
  id: string
  restaurantId: string
  customerName: string
  rating: number
  comment: string
  date: string
}

export type Place = {
  id: string
  name: string
  description: string
  country: string
  city: string
  image: string
  rating: number
  category: "historical" | "nature" | "cultural" | "adventure"
}

export const mockRestaurants: Restaurant[] = [
  {
    id: "rest-1",
    name: "La Bella Vista",
    description: "Authentic Italian cuisine with a modern twist",
    cuisine: "Italian",
    address: "123 Harbor View, Downtown",
    phone: "+1 234 567 8900",
    priceRange: "$$$",
    rating: 4.8,
    image: "/elegant-italian-restaurant.png",
    seatingCapacity: 80,
  },
  {
    id: "rest-2",
    name: "Sakura Garden",
    description: "Traditional Japanese dining experience",
    cuisine: "Japanese",
    address: "456 Cherry Blossom Lane",
    phone: "+1 234 567 8901",
    priceRange: "$$$$",
    rating: 4.9,
    image: "/japanese-restaurant-sushi-bar.jpg",
    seatingCapacity: 60,
  },
  {
    id: "rest-3",
    name: "Spice Route",
    description: "Vibrant Indian flavors and spices",
    cuisine: "Indian",
    address: "789 Curry Street, Midtown",
    phone: "+1 234 567 8902",
    priceRange: "$$",
    rating: 4.6,
    image: "/indian-restaurant-colorful-interior.jpg",
    seatingCapacity: 100,
  },
]

export const mockMenuItems: MenuItem[] = [
  {
    id: "menu-1",
    restaurantId: "rest-1",
    name: "Margherita Pizza",
    description: "Fresh mozzarella, tomatoes, basil",
    price: 18.99,
    category: "main",
    image: "/margherita-pizza.png",
    available: true,
  },
  {
    id: "menu-2",
    restaurantId: "rest-1",
    name: "Tiramisu",
    description: "Classic Italian dessert",
    price: 9.99,
    category: "dessert",
    image: "/classic-tiramisu.png",
    available: true,
  },
  {
    id: "menu-3",
    restaurantId: "rest-1",
    name: "Bruschetta",
    description: "Toasted bread with tomatoes and garlic",
    price: 12.99,
    category: "appetizer",
    image: "/classic-bruschetta.png",
    available: true,
  },
]

export const mockReservations: Reservation[] = [
  {
    id: "res-1",
    restaurantId: "rest-1",
    customerName: "John Doe",
    customerEmail: "john@example.com",
    customerPhone: "+1 234 567 1111",
    date: "2025-01-15",
    time: "19:00",
    guests: 4,
    status: "confirmed",
    specialRequests: "Window seat preferred",
  },
  {
    id: "res-2",
    restaurantId: "rest-1",
    customerName: "Jane Smith",
    customerEmail: "jane@example.com",
    customerPhone: "+1 234 567 2222",
    date: "2025-01-16",
    time: "20:00",
    guests: 2,
    status: "pending",
  },
]

export const mockExternalServices: ExternalService[] = [
  {
    id: "tour-1",
    type: "tour",
    name: "City Historical Tour",
    description: "3-hour guided tour of historical landmarks",
    price: 45,
    image: "/city-tour-bus.jpg",
    rating: 4.7,
    available: true,
  },
  {
    id: "hotel-1",
    type: "hotel",
    name: "Grand Palace Hotel",
    description: "5-star luxury accommodation",
    price: 250,
    image: "/luxury-hotel-exterior.png",
    rating: 4.9,
    available: true,
  },
  {
    id: "taxi-1",
    type: "taxi",
    name: "Premium Taxi Service",
    description: "24/7 reliable transportation",
    price: 25,
    image: "/taxi-cab-service.jpg",
    rating: 4.5,
    available: true,
  },
]

export const mockReviews: Review[] = [
  {
    id: "rev-1",
    restaurantId: "rest-1",
    customerName: "Alice Johnson",
    rating: 5,
    comment: "Absolutely amazing! The food was incredible and service was top-notch.",
    date: "2025-01-10",
  },
  {
    id: "rev-2",
    restaurantId: "rest-1",
    customerName: "Bob Wilson",
    rating: 4,
    comment: "Great atmosphere and delicious pasta. Will definitely come back!",
    date: "2025-01-08",
  },
  {
    id: "rev-3",
    restaurantId: "rest-2",
    customerName: "Carol Martinez",
    rating: 5,
    comment: "Best sushi in town! Fresh ingredients and beautiful presentation.",
    date: "2025-01-12",
  },
]

export const mockPlaces: Place[] = [
  {
    id: "place-1",
    name: "Grand Canyon",
    description: "Breathtaking natural wonder with stunning views",
    country: "USA",
    city: "Arizona",
    image: "/grand-canyon.png",
    rating: 4.9,
    category: "nature",
  },
  {
    id: "place-2",
    name: "Eiffel Tower",
    description: "Iconic landmark and symbol of Paris",
    country: "France",
    city: "Paris",
    image: "/eiffel-tower.png",
    rating: 4.8,
    category: "historical",
  },
  {
    id: "place-3",
    name: "Machu Picchu",
    description: "Ancient Incan citadel in the Andes Mountains",
    country: "Peru",
    city: "Cusco",
    image: "/machu-picchu-ancient-city.png",
    rating: 4.9,
    category: "historical",
  },
  {
    id: "place-4",
    name: "Kyoto Temples",
    description: "Traditional Japanese temples and gardens",
    country: "Japan",
    city: "Kyoto",
    image: "/kyoto-temples.jpg",
    rating: 4.7,
    category: "cultural",
  },
]
