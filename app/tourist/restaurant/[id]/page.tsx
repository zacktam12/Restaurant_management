"use client"

import { useEffect, useState, use } from "react"
import { useAuth } from "@/lib/auth-context"
import { useRouter } from "next/navigation"
import { TouristNav } from "@/components/tourist-nav"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Badge } from "@/components/ui/badge"
import { MapPin, Phone, Star, ArrowLeft, Users } from "lucide-react"
import { mockRestaurants, mockMenuItems, type Restaurant, type MenuItem } from "@/lib/mock-data"
import Link from "next/link"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

export default function RestaurantDetail({ params }: { params: Promise<{ id: string }> }) {
  const resolvedParams = use(params)
  const { user, isLoading } = useAuth()
  const router = useRouter()
  const [restaurant, setRestaurant] = useState<Restaurant | null>(null)
  const [menuItems, setMenuItems] = useState<MenuItem[]>([])
  const [isBookingOpen, setIsBookingOpen] = useState(false)

  useEffect(() => {
    if (!isLoading && (!user || (user.role !== "tourist" && user.role !== "customer"))) {
      router.push("/")
      return
    }

    const foundRestaurant = mockRestaurants.find((r) => r.id === resolvedParams.id)
    setRestaurant(foundRestaurant || null)

    const restaurantMenu = mockMenuItems.filter((item) => item.restaurantId === resolvedParams.id)
    setMenuItems(restaurantMenu)
  }, [resolvedParams.id, user, isLoading, router])

  if (isLoading || !user || !restaurant) {
    return null
  }

  const categorizedMenu = {
    appetizer: menuItems.filter((item) => item.category === "appetizer"),
    main: menuItems.filter((item) => item.category === "main"),
    dessert: menuItems.filter((item) => item.category === "dessert"),
    beverage: menuItems.filter((item) => item.category === "beverage"),
  }

  return (
    <div className="min-h-screen bg-gradient-to-b from-orange-50/30 via-white to-amber-50/30">
      <TouristNav active="browse" />

      <main className="container mx-auto px-4 py-8">
        <Link href="/tourist">
          <Button variant="ghost" className="mb-6 gap-2">
            <ArrowLeft className="w-4 h-4" />
            Back to Browse
          </Button>
        </Link>

        {/* Restaurant Header */}
        <div className="grid gap-8 lg:grid-cols-3 mb-8">
          <div className="lg:col-span-2">
            <Card className="overflow-hidden border-0 ring-1 ring-black/5 shadow-lg">
              <div className="aspect-[21/9] relative bg-muted">
                <img
                  src={restaurant.image || "/placeholder.svg"}
                  alt={restaurant.name}
                  className="object-cover w-full h-full"
                />
                <div className="absolute top-4 right-4 bg-white/95 backdrop-blur-sm px-4 py-2 rounded-full flex items-center gap-2 shadow-lg">
                  <Star className="w-5 h-5 fill-amber-500 text-amber-500" />
                  <span className="font-bold text-lg">{restaurant.rating}</span>
                </div>
              </div>
              <CardHeader>
                <div className="flex items-start justify-between">
                  <div>
                    <CardTitle className="text-4xl mb-2">{restaurant.name}</CardTitle>
                    <CardDescription className="text-lg">{restaurant.description}</CardDescription>
                  </div>
                  <Badge variant="secondary" className="text-lg px-4 py-1.5">
                    {restaurant.cuisine}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex items-start gap-3 text-base">
                  <MapPin className="w-5 h-5 mt-0.5 text-primary" />
                  <span>{restaurant.address}</span>
                </div>
                <div className="flex items-center gap-3 text-base">
                  <Phone className="w-5 h-5 text-primary" />
                  <span>{restaurant.phone}</span>
                </div>
                <div className="flex items-center gap-3 text-base">
                  <Users className="w-5 h-5 text-primary" />
                  <span>Capacity: {restaurant.seatingCapacity} seats</span>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Booking Card */}
          <Card className="border-0 ring-1 ring-black/5 shadow-lg h-fit sticky top-24">
            <CardHeader>
              <CardTitle className="text-2xl">Reserve a Table</CardTitle>
              <CardDescription>Book your dining experience</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="date">Date</Label>
                <Input id="date" type="date" className="h-11" />
              </div>
              <div className="space-y-2">
                <Label htmlFor="time">Time</Label>
                <Select>
                  <SelectTrigger id="time" className="h-11">
                    <SelectValue placeholder="Select time" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="17:00">5:00 PM</SelectItem>
                    <SelectItem value="17:30">5:30 PM</SelectItem>
                    <SelectItem value="18:00">6:00 PM</SelectItem>
                    <SelectItem value="18:30">6:30 PM</SelectItem>
                    <SelectItem value="19:00">7:00 PM</SelectItem>
                    <SelectItem value="19:30">7:30 PM</SelectItem>
                    <SelectItem value="20:00">8:00 PM</SelectItem>
                    <SelectItem value="20:30">8:30 PM</SelectItem>
                    <SelectItem value="21:00">9:00 PM</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label htmlFor="guests">Number of Guests</Label>
                <Select>
                  <SelectTrigger id="guests" className="h-11">
                    <SelectValue placeholder="Select guests" />
                  </SelectTrigger>
                  <SelectContent>
                    {[1, 2, 3, 4, 5, 6, 7, 8].map((num) => (
                      <SelectItem key={num} value={num.toString()}>
                        {num} {num === 1 ? "Guest" : "Guests"}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label htmlFor="special">Special Requests (Optional)</Label>
                <Textarea id="special" placeholder="Any dietary requirements or preferences..." rows={3} />
              </div>

              <Dialog open={isBookingOpen} onOpenChange={setIsBookingOpen}>
                <DialogTrigger asChild>
                  <Button className="w-full h-12 text-base font-medium bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700">
                    Book Now
                  </Button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>Booking Confirmed!</DialogTitle>
                    <DialogDescription>Your reservation has been successfully submitted.</DialogDescription>
                  </DialogHeader>
                  <div className="py-4">
                    <p className="text-center text-muted-foreground">
                      You will receive a confirmation email shortly. Check your bookings page for details.
                    </p>
                  </div>
                  <div className="flex gap-3">
                    <Button variant="outline" onClick={() => setIsBookingOpen(false)} className="flex-1">
                      Close
                    </Button>
                    <Link href="/tourist/bookings" className="flex-1">
                      <Button className="w-full">View Bookings</Button>
                    </Link>
                  </div>
                </DialogContent>
              </Dialog>
            </CardContent>
          </Card>
        </div>

        {/* Menu */}
        <Card className="border-0 ring-1 ring-black/5 shadow-lg">
          <CardHeader>
            <CardTitle className="text-3xl">Our Menu</CardTitle>
            <CardDescription className="text-base">Explore our delicious offerings</CardDescription>
          </CardHeader>
          <CardContent>
            <Tabs defaultValue="main" className="w-full">
              <TabsList className="grid w-full grid-cols-4 mb-6">
                <TabsTrigger value="appetizer">Appetizers</TabsTrigger>
                <TabsTrigger value="main">Main Courses</TabsTrigger>
                <TabsTrigger value="dessert">Desserts</TabsTrigger>
                <TabsTrigger value="beverage">Beverages</TabsTrigger>
              </TabsList>

              {(["appetizer", "main", "dessert", "beverage"] as const).map((category) => (
                <TabsContent key={category} value={category}>
                  {categorizedMenu[category].length > 0 ? (
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                      {categorizedMenu[category].map((item) => (
                        <Card key={item.id} className="overflow-hidden">
                          <div className="aspect-video relative bg-muted">
                            <img
                              src={item.image || "/placeholder.svg"}
                              alt={item.name}
                              className="object-cover w-full h-full"
                            />
                          </div>
                          <CardHeader>
                            <div className="flex items-start justify-between gap-2">
                              <CardTitle className="text-lg">{item.name}</CardTitle>
                              <span className="text-lg font-bold text-primary whitespace-nowrap">
                                ${item.price.toFixed(2)}
                              </span>
                            </div>
                            <CardDescription>{item.description}</CardDescription>
                          </CardHeader>
                        </Card>
                      ))}
                    </div>
                  ) : (
                    <div className="text-center py-12 text-muted-foreground">
                      <p>No items in this category yet</p>
                    </div>
                  )}
                </TabsContent>
              ))}
            </Tabs>
          </CardContent>
        </Card>
      </main>
    </div>
  )
}
