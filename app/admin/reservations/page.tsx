"use client"

import { useEffect, useState } from "react"
import { useAuth } from "@/lib/auth-context"
import { useRouter } from "next/navigation"
import { AdminNav } from "@/components/admin-nav"
import { Card, CardContent } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Calendar, Clock, Users, Phone, Mail, Search, CheckCircle, XCircle, AlertCircle } from "lucide-react"
import { mockReservations, type Reservation } from "@/lib/mock-data"
import { Badge } from "@/components/ui/badge"
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs"

export default function ReservationsManagement() {
  const { user, isLoading } = useAuth()
  const router = useRouter()
  const [reservations, setReservations] = useState<Reservation[]>(mockReservations)
  const [searchQuery, setSearchQuery] = useState("")
  const [statusFilter, setStatusFilter] = useState<string>("all")

  useEffect(() => {
    if (!isLoading && (!user || (user.role !== "admin" && user.role !== "manager"))) {
      router.push("/")
    }
  }, [user, isLoading, router])

  if (isLoading || !user) {
    return null
  }

  const filteredReservations = reservations.filter((reservation) => {
    const matchesSearch =
      reservation.customerName.toLowerCase().includes(searchQuery.toLowerCase()) ||
      reservation.customerEmail.toLowerCase().includes(searchQuery.toLowerCase())
    const matchesStatus = statusFilter === "all" || reservation.status === statusFilter
    return matchesSearch && matchesStatus
  })

  const handleStatusChange = (id: string, newStatus: Reservation["status"]) => {
    setReservations(reservations.map((r) => (r.id === id ? { ...r, status: newStatus } : r)))
  }

  return (
    <div className="min-h-screen bg-muted/30">
      <AdminNav active="reservations" />

      <main className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-4xl font-bold mb-2 text-balance">Reservations</h1>
          <p className="text-muted-foreground text-lg">Manage and track all restaurant reservations</p>
        </div>

        {/* Search and Filter */}
        <Card className="mb-6">
          <CardContent className="pt-6">
            <div className="flex flex-col sm:flex-row gap-4">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                <Input
                  placeholder="Search by customer name or email..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-10"
                />
              </div>
              <Tabs value={statusFilter} onValueChange={setStatusFilter} className="w-full sm:w-auto">
                <TabsList>
                  <TabsTrigger value="all">All</TabsTrigger>
                  <TabsTrigger value="pending">Pending</TabsTrigger>
                  <TabsTrigger value="confirmed">Confirmed</TabsTrigger>
                  <TabsTrigger value="cancelled">Cancelled</TabsTrigger>
                </TabsList>
              </Tabs>
            </div>
          </CardContent>
        </Card>

        {/* Reservations List */}
        <div className="space-y-4">
          {filteredReservations.map((reservation) => (
            <Card key={reservation.id} className="hover:shadow-md transition-shadow">
              <CardContent className="p-6">
                <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                  <div className="flex items-start gap-4 flex-1">
                    <div
                      className={`w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 ${
                        reservation.status === "confirmed"
                          ? "bg-green-100 text-green-600"
                          : reservation.status === "pending"
                            ? "bg-yellow-100 text-yellow-600"
                            : "bg-red-100 text-red-600"
                      }`}
                    >
                      {reservation.status === "confirmed" ? (
                        <CheckCircle className="w-6 h-6" />
                      ) : reservation.status === "pending" ? (
                        <AlertCircle className="w-6 h-6" />
                      ) : (
                        <XCircle className="w-6 h-6" />
                      )}
                    </div>

                    <div className="flex-1 space-y-3">
                      <div>
                        <h3 className="text-xl font-semibold mb-1">{reservation.customerName}</h3>
                        <div className="flex flex-wrap gap-4 text-sm text-muted-foreground">
                          <span className="flex items-center gap-1">
                            <Mail className="w-4 h-4" />
                            {reservation.customerEmail}
                          </span>
                          <span className="flex items-center gap-1">
                            <Phone className="w-4 h-4" />
                            {reservation.customerPhone}
                          </span>
                        </div>
                      </div>

                      <div className="flex flex-wrap gap-4">
                        <Badge variant="outline" className="gap-1">
                          <Calendar className="w-3 h-3" />
                          {reservation.date}
                        </Badge>
                        <Badge variant="outline" className="gap-1">
                          <Clock className="w-3 h-3" />
                          {reservation.time}
                        </Badge>
                        <Badge variant="outline" className="gap-1">
                          <Users className="w-3 h-3" />
                          {reservation.guests} guests
                        </Badge>
                      </div>

                      {reservation.specialRequests && (
                        <p className="text-sm text-muted-foreground italic">
                          Special requests: {reservation.specialRequests}
                        </p>
                      )}
                    </div>
                  </div>

                  <div className="flex flex-col gap-2 lg:items-end">
                    <Badge
                      className={`${
                        reservation.status === "confirmed"
                          ? "bg-green-100 text-green-700 hover:bg-green-200"
                          : reservation.status === "pending"
                            ? "bg-yellow-100 text-yellow-700 hover:bg-yellow-200"
                            : "bg-red-100 text-red-700 hover:bg-red-200"
                      }`}
                    >
                      {reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1)}
                    </Badge>

                    {reservation.status === "pending" && (
                      <div className="flex gap-2">
                        <Button
                          size="sm"
                          onClick={() => handleStatusChange(reservation.id, "confirmed")}
                          className="bg-green-600 hover:bg-green-700"
                        >
                          Confirm
                        </Button>
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => handleStatusChange(reservation.id, "cancelled")}
                          className="text-destructive"
                        >
                          Cancel
                        </Button>
                      </div>
                    )}
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}

          {filteredReservations.length === 0 && (
            <Card>
              <CardContent className="py-12 text-center">
                <Calendar className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                <h3 className="text-lg font-semibold mb-2">No reservations found</h3>
                <p className="text-muted-foreground">Try adjusting your search or filters</p>
              </CardContent>
            </Card>
          )}
        </div>
      </main>
    </div>
  )
}
