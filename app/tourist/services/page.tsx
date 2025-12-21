"use client"

import { useEffect, useState } from "react"
import { useAuth } from "@/lib/auth-context"
import { useRouter } from "next/navigation"
import { TouristNav } from "@/components/tourist-nav"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Star, Car, Building2, Compass } from "lucide-react"
import { mockExternalServices, type ExternalService } from "@/lib/mock-data"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"

export default function TouristServices() {
  const { user, isLoading } = useAuth()
  const router = useRouter()
  const [services, setServices] = useState<ExternalService[]>(mockExternalServices)

  useEffect(() => {
    if (!isLoading && (!user || (user.role !== "tourist" && user.role !== "customer"))) {
      router.push("/")
    }
  }, [user, isLoading, router])

  if (isLoading || !user) {
    return null
  }

  const tours = services.filter((s) => s.type === "tour")
  const hotels = services.filter((s) => s.type === "hotel")
  const taxis = services.filter((s) => s.type === "taxi")

  const ServiceCard = ({ service }: { service: ExternalService }) => (
    <Card className="overflow-hidden border-0 ring-1 ring-black/5 shadow-lg hover:shadow-xl transition-all group">
      <div className="aspect-video relative bg-muted overflow-hidden">
        <img
          src={service.image || "/placeholder.svg"}
          alt={service.name}
          className="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300"
        />
        <div className="absolute top-4 right-4 bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-full flex items-center gap-1.5 shadow-lg">
          <Star className="w-4 h-4 fill-amber-500 text-amber-500" />
          <span className="font-semibold text-sm">{service.rating}</span>
        </div>
        {service.available && (
          <Badge className="absolute bottom-4 left-4 bg-green-100 text-green-700 shadow-lg">Available</Badge>
        )}
      </div>

      <CardHeader>
        <div className="flex items-start justify-between gap-2">
          <CardTitle className="text-xl">{service.name}</CardTitle>
          <span className="text-xl font-bold text-primary whitespace-nowrap">${service.price}</span>
        </div>
        <CardDescription className="text-base">{service.description}</CardDescription>
      </CardHeader>

      <CardContent>
        <Button className="w-full h-11 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700">
          Book Now
        </Button>
      </CardContent>
    </Card>
  )

  return (
    <div className="min-h-screen bg-gradient-to-b from-orange-50/30 via-white to-amber-50/30">
      <TouristNav active="services" />

      <main className="container mx-auto px-4 py-8">
        <div className="text-center mb-12">
          <h1 className="text-5xl font-bold mb-4 text-balance bg-gradient-to-r from-orange-600 to-amber-600 bg-clip-text text-transparent">
            Additional Services
          </h1>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto text-pretty">
            Complete your travel experience with tours, accommodation, and transportation
          </p>
        </div>

        <Tabs defaultValue="tours" className="w-full">
          <TabsList className="grid w-full max-w-xl mx-auto grid-cols-3 mb-8 h-12">
            <TabsTrigger value="tours" className="gap-2">
              <Compass className="w-4 h-4" />
              Tours
            </TabsTrigger>
            <TabsTrigger value="hotels" className="gap-2">
              <Building2 className="w-4 h-4" />
              Hotels
            </TabsTrigger>
            <TabsTrigger value="taxis" className="gap-2">
              <Car className="w-4 h-4" />
              Taxis
            </TabsTrigger>
          </TabsList>

          <TabsContent value="tours">
            <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
              {tours.map((service) => (
                <ServiceCard key={service.id} service={service} />
              ))}
            </div>
          </TabsContent>

          <TabsContent value="hotels">
            <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
              {hotels.map((service) => (
                <ServiceCard key={service.id} service={service} />
              ))}
            </div>
          </TabsContent>

          <TabsContent value="taxis">
            <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
              {taxis.map((service) => (
                <ServiceCard key={service.id} service={service} />
              ))}
            </div>
          </TabsContent>
        </Tabs>
      </main>
    </div>
  )
}
