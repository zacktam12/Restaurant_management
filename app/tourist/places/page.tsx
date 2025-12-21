"use client"

import { useEffect, useState } from "react"
import { useAuth } from "@/lib/auth-context"
import { useRouter } from "next/navigation"
import { TouristNav } from "@/components/tourist-nav"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Star, MapPin, Search, Globe } from "lucide-react"
import { mockPlaces, type Place } from "@/lib/mock-data"
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs"

export default function TouristPlaces() {
  const { user, isLoading } = useAuth()
  const router = useRouter()
  const [places, setPlaces] = useState<Place[]>(mockPlaces)
  const [searchQuery, setSearchQuery] = useState("")
  const [categoryFilter, setCategoryFilter] = useState<string>("all")

  useEffect(() => {
    if (!isLoading && (!user || (user.role !== "tourist" && user.role !== "customer"))) {
      router.push("/")
    }
  }, [user, isLoading, router])

  if (isLoading || !user) {
    return null
  }

  const filteredPlaces = places.filter((place) => {
    const matchesSearch =
      place.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      place.city.toLowerCase().includes(searchQuery.toLowerCase()) ||
      place.country.toLowerCase().includes(searchQuery.toLowerCase())
    const matchesCategory = categoryFilter === "all" || place.category === categoryFilter
    return matchesSearch && matchesCategory
  })

  return (
    <div className="min-h-screen bg-gradient-to-b from-orange-50/30 via-white to-amber-50/30">
      <TouristNav active="places" />

      <main className="container mx-auto px-4 py-8">
        <div className="text-center mb-12">
          <h1 className="text-5xl font-bold mb-4 text-balance bg-gradient-to-r from-orange-600 to-amber-600 bg-clip-text text-transparent">
            Browse Places
          </h1>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto text-pretty">
            Discover amazing destinations around the world
          </p>
        </div>

        <Card className="mb-8">
          <CardContent className="pt-6">
            <div className="flex flex-col sm:flex-row gap-4">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                <Input
                  placeholder="Search places, cities, or countries..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-10"
                />
              </div>
              <Tabs value={categoryFilter} onValueChange={setCategoryFilter} className="w-full sm:w-auto">
                <TabsList>
                  <TabsTrigger value="all">All</TabsTrigger>
                  <TabsTrigger value="historical">Historical</TabsTrigger>
                  <TabsTrigger value="nature">Nature</TabsTrigger>
                  <TabsTrigger value="cultural">Cultural</TabsTrigger>
                  <TabsTrigger value="adventure">Adventure</TabsTrigger>
                </TabsList>
              </Tabs>
            </div>
          </CardContent>
        </Card>

        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
          {filteredPlaces.map((place) => (
            <Card
              key={place.id}
              className="overflow-hidden border-0 ring-1 ring-black/5 shadow-lg hover:shadow-xl transition-all group"
            >
              <div className="aspect-video relative bg-muted overflow-hidden">
                <img
                  src={place.image || "/placeholder.svg"}
                  alt={place.name}
                  className="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300"
                />
                <div className="absolute top-4 right-4 bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-full flex items-center gap-1.5 shadow-lg">
                  <Star className="w-4 h-4 fill-amber-500 text-amber-500" />
                  <span className="font-semibold text-sm">{place.rating}</span>
                </div>
                <Badge className="absolute bottom-4 left-4 bg-primary/90 backdrop-blur-sm capitalize">
                  {place.category}
                </Badge>
              </div>

              <CardHeader>
                <CardTitle className="text-xl">{place.name}</CardTitle>
                <CardDescription className="flex items-center gap-2 text-base">
                  <MapPin className="w-4 h-4" />
                  {place.city}, {place.country}
                </CardDescription>
              </CardHeader>

              <CardContent>
                <p className="text-muted-foreground mb-4">{place.description}</p>
                <Button className="w-full h-11 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700">
                  <Globe className="w-4 h-4 mr-2" />
                  View Tours
                </Button>
              </CardContent>
            </Card>
          ))}
        </div>

        {filteredPlaces.length === 0 && (
          <Card>
            <CardContent className="py-12 text-center">
              <MapPin className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
              <h3 className="text-lg font-semibold mb-2">No places found</h3>
              <p className="text-muted-foreground">Try adjusting your search or filters</p>
            </CardContent>
          </Card>
        )}
      </main>
    </div>
  )
}
