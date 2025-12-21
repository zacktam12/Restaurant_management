"use client"

import { useEffect } from "react"
import { useAuth } from "@/lib/auth-context"
import { useRouter } from "next/navigation"
import { AdminNav } from "@/components/admin-nav"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Calendar, Users, TrendingUp, DollarSign, Clock, CheckCircle, XCircle, AlertCircle } from "lucide-react"
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, LineChart, Line } from "recharts"
import { mockReservations } from "@/lib/mock-data"

const revenueData = [
  { month: "Jan", revenue: 12500 },
  { month: "Feb", revenue: 15200 },
  { month: "Mar", revenue: 18800 },
  { month: "Apr", revenue: 16400 },
  { month: "May", revenue: 21000 },
  { month: "Jun", revenue: 24500 },
]

const bookingData = [
  { day: "Mon", bookings: 24 },
  { day: "Tue", bookings: 32 },
  { day: "Wed", bookings: 28 },
  { day: "Thu", bookings: 35 },
  { day: "Fri", bookings: 45 },
  { day: "Sat", bookings: 52 },
  { day: "Sun", bookings: 48 },
]

export default function AdminDashboard() {
  const { user, isLoading } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (!isLoading && (!user || (user.role !== "admin" && user.role !== "manager"))) {
      router.push("/")
    }
  }, [user, isLoading, router])

  if (isLoading || !user) {
    return null
  }

  const totalReservations = mockReservations.length
  const confirmedReservations = mockReservations.filter((r) => r.status === "confirmed").length
  const pendingReservations = mockReservations.filter((r) => r.status === "pending").length

  return (
    <div className="min-h-screen bg-muted/30">
      <AdminNav active="dashboard" />

      <main className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-4xl font-bold mb-2 text-balance">Dashboard Overview</h1>
          <p className="text-muted-foreground text-lg">Welcome back! Here's what's happening today.</p>
        </div>

        {/* Stats Grid */}
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-8">
          <Card className="border-l-4 border-l-primary">
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">Total Reservations</CardTitle>
              <Calendar className="w-5 h-5 text-primary" />
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">{totalReservations}</div>
              <p className="text-xs text-muted-foreground mt-1">
                <span className="text-green-600 font-medium">+12%</span> from last month
              </p>
            </CardContent>
          </Card>

          <Card className="border-l-4 border-l-chart-2">
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">Today's Guests</CardTitle>
              <Users className="w-5 h-5 text-chart-2" />
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">42</div>
              <p className="text-xs text-muted-foreground mt-1">Across 18 reservations</p>
            </CardContent>
          </Card>

          <Card className="border-l-4 border-l-chart-3">
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">Revenue (MTD)</CardTitle>
              <DollarSign className="w-5 h-5 text-chart-3" />
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">$24,500</div>
              <p className="text-xs text-muted-foreground mt-1">
                <span className="text-green-600 font-medium">+18%</span> from last month
              </p>
            </CardContent>
          </Card>

          <Card className="border-l-4 border-l-chart-4">
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">Average Rating</CardTitle>
              <TrendingUp className="w-5 h-5 text-chart-4" />
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">4.8</div>
              <p className="text-xs text-muted-foreground mt-1">From 156 reviews</p>
            </CardContent>
          </Card>
        </div>

        {/* Charts */}
        <div className="grid gap-6 lg:grid-cols-2 mb-8">
          <Card>
            <CardHeader>
              <CardTitle>Revenue Overview</CardTitle>
              <CardDescription>Monthly revenue for the past 6 months</CardDescription>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={300}>
                <LineChart data={revenueData}>
                  <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                  <XAxis dataKey="month" className="text-xs" />
                  <YAxis className="text-xs" />
                  <Tooltip
                    contentStyle={{ backgroundColor: "hsl(var(--card))", border: "1px solid hsl(var(--border))" }}
                  />
                  <Line type="monotone" dataKey="revenue" stroke="hsl(var(--primary))" strokeWidth={2} />
                </LineChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Weekly Bookings</CardTitle>
              <CardDescription>Number of reservations by day</CardDescription>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={300}>
                <BarChart data={bookingData}>
                  <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                  <XAxis dataKey="day" className="text-xs" />
                  <YAxis className="text-xs" />
                  <Tooltip
                    contentStyle={{ backgroundColor: "hsl(var(--card))", border: "1px solid hsl(var(--border))" }}
                  />
                  <Bar dataKey="bookings" fill="hsl(var(--chart-2))" radius={[8, 8, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>
        </div>

        {/* Recent Reservations */}
        <Card>
          <CardHeader>
            <CardTitle>Recent Reservations</CardTitle>
            <CardDescription>Latest booking requests and confirmations</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {mockReservations.map((reservation) => (
                <div
                  key={reservation.id}
                  className="flex items-center justify-between p-4 bg-muted/50 rounded-lg hover:bg-muted transition-colors"
                >
                  <div className="flex items-center gap-4">
                    <div
                      className={`w-10 h-10 rounded-full flex items-center justify-center ${
                        reservation.status === "confirmed"
                          ? "bg-green-100 text-green-600"
                          : reservation.status === "pending"
                            ? "bg-yellow-100 text-yellow-600"
                            : "bg-red-100 text-red-600"
                      }`}
                    >
                      {reservation.status === "confirmed" ? (
                        <CheckCircle className="w-5 h-5" />
                      ) : reservation.status === "pending" ? (
                        <AlertCircle className="w-5 h-5" />
                      ) : (
                        <XCircle className="w-5 h-5" />
                      )}
                    </div>
                    <div>
                      <p className="font-semibold">{reservation.customerName}</p>
                      <p className="text-sm text-muted-foreground">
                        {reservation.guests} guests • {reservation.date} at {reservation.time}
                      </p>
                    </div>
                  </div>
                  <div className="flex items-center gap-4">
                    <div className="text-right">
                      <span
                        className={`text-xs font-medium px-3 py-1 rounded-full ${
                          reservation.status === "confirmed"
                            ? "bg-green-100 text-green-700"
                            : reservation.status === "pending"
                              ? "bg-yellow-100 text-yellow-700"
                              : "bg-red-100 text-red-700"
                        }`}
                      >
                        {reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1)}
                      </span>
                    </div>
                    <Clock className="w-4 h-4 text-muted-foreground" />
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </main>
    </div>
  )
}
