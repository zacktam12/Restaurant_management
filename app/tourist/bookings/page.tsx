"use client"

import { useEffect, useState } from "react"
import { useAuth } from "@/lib/auth-context"
import { useRouter } from "next/navigation"
import { TouristNav } from "@/components/tourist-nav"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Calendar, Clock, Users, MapPin, CheckCircle, AlertCircle, XCircle } from "lucide-react"
import { mockReservations, mockRestaurants, type Reservation } from "@/lib/mock-data"

export default function TouristBookings() {
  const { user, isLoading } = useAuth()
  const router = useRouter()
  const [bookings, setBookings] = useState<Reservation[]>([])

  useEffect(() => {
    if (!isLoading && (!user || (user.role !== "tourist" && user.role !== "customer"))) {
      router.push("/")
      return
    }

    // In a real app, filter by user email
    setBookings(mockReservations)
  }, [user, isLoading, router])

  if (isLoading || !user) {
    return null
  }

  const upcomingBookings = bookings.filter((b) => b.status === "confirmed" || b.status === "pending")
  const pastBookings = bookings.filter((b) => b.status === "completed" || b.status === "cancelled")

  return (
    <div className="min-h-screen bg-gradient-to-b from-orange-50/30 via-white to-amber-50/30">
      <TouristNav active="bookings" />

      <main className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-4xl font-bold mb-2 text-balance">My Bookings</h1>
          <p className="text-muted-foreground text-lg">View and manage your restaurant reservations</p>
        </div>

        {/* Upcoming Bookings */}
        <div className="mb-12">
          <h2 className="text-2xl font-bold mb-6">Upcoming Reservations</h2>
          {upcomingBookings.length > 0 ? (
            <div className="grid gap-6 md:grid-cols-2">
              {upcomingBookings.map((booking) => {
                const restaurant = mockRestaurants.find((r) => r.id === booking.restaurantId)
                return (
                  <Card
                    key={booking.id}
                    className="border-0 ring-1 ring-black/5 shadow-lg hover:shadow-xl transition-shadow"
                  >
                    <CardHeader>
                      <div className="flex items-start justify-between">
                        <div>
                          <CardTitle className="text-2xl mb-1">{restaurant?.name}</CardTitle>
                          <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <MapPin className="w-4 h-4" />
                            <span>{restaurant?.address}</span>
                          </div>
                        </div>
                        <Badge
                          className={`${
                            booking.status === "confirmed"
                              ? "bg-green-100 text-green-700"
                              : "bg-yellow-100 text-yellow-700"
                          }`}
                        >
                          {booking.status === "confirmed" ? (
                            <CheckCircle className="w-3 h-3 mr-1" />
                          ) : (
                            <AlertCircle className="w-3 h-3 mr-1" />
                          )}
                          {booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}
                        </Badge>
                      </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                      <div className="grid grid-cols-3 gap-4">
                        <div className="flex items-center gap-2">
                          <Calendar className="w-5 h-5 text-primary" />
                          <div>
                            <p className="text-xs text-muted-foreground">Date</p>
                            <p className="font-semibold">{booking.date}</p>
                          </div>
                        </div>
                        <div className="flex items-center gap-2">
                          <Clock className="w-5 h-5 text-primary" />
                          <div>
                            <p className="text-xs text-muted-foreground">Time</p>
                            <p className="font-semibold">{booking.time}</p>
                          </div>
                        </div>
                        <div className="flex items-center gap-2">
                          <Users className="w-5 h-5 text-primary" />
                          <div>
                            <p className="text-xs text-muted-foreground">Guests</p>
                            <p className="font-semibold">{booking.guests}</p>
                          </div>
                        </div>
                      </div>

                      {booking.specialRequests && (
                        <div className="p-3 bg-muted/50 rounded-lg">
                          <p className="text-sm text-muted-foreground">
                            <span className="font-medium">Special Requests:</span> {booking.specialRequests}
                          </p>
                        </div>
                      )}

                      <div className="flex gap-2">
                        <Button variant="outline" className="flex-1 bg-transparent">
                          Modify
                        </Button>
                        <Button
                          variant="outline"
                          className="flex-1 text-destructive hover:text-destructive bg-transparent"
                        >
                          Cancel
                        </Button>
                      </div>
                    </CardContent>
                  </Card>
                )
              })}
            </div>
          ) : (
            <Card className="border-0 ring-1 ring-black/5">
              <CardContent className="py-12 text-center">
                <Calendar className="w-16 h-16 text-muted-foreground mx-auto mb-4" />
                <h3 className="text-xl font-semibold mb-2">No upcoming bookings</h3>
                <p className="text-muted-foreground mb-6">Start exploring restaurants to make your first reservation</p>
                <Button
                  onClick={() => router.push("/tourist")}
                  className="bg-gradient-to-r from-orange-500 to-amber-600"
                >
                  Browse Restaurants
                </Button>
              </CardContent>
            </Card>
          )}
        </div>

        {/* Past Bookings */}
        {pastBookings.length > 0 && (
          <div>
            <h2 className="text-2xl font-bold mb-6">Past Reservations</h2>
            <div className="space-y-4">
              {pastBookings.map((booking) => {
                const restaurant = mockRestaurants.find((r) => r.id === booking.restaurantId)
                return (
                  <Card key={booking.id} className="border-0 ring-1 ring-black/5 opacity-75">
                    <CardContent className="py-4">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                          <div
                            className={`w-10 h-10 rounded-full flex items-center justify-center ${
                              booking.status === "completed" ? "bg-green-100 text-green-600" : "bg-red-100 text-red-600"
                            }`}
                          >
                            {booking.status === "completed" ? (
                              <CheckCircle className="w-5 h-5" />
                            ) : (
                              <XCircle className="w-5 h-5" />
                            )}
                          </div>
                          <div>
                            <p className="font-semibold">{restaurant?.name}</p>
                            <p className="text-sm text-muted-foreground">
                              {booking.date} at {booking.time} • {booking.guests} guests
                            </p>
                          </div>
                        </div>
                        <Badge variant="outline">{booking.status === "completed" ? "Completed" : "Cancelled"}</Badge>
                      </div>
                    </CardContent>
                  </Card>
                )
              })}
            </div>
          </div>
        )}
      </main>
    </div>
  )
}
