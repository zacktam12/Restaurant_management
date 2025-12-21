"use client"

import { useEffect, useState } from "react"
import { useAuth } from "@/lib/auth-context"
import { useRouter } from "next/navigation"
import { TouristNav } from "@/components/tourist-nav"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { MapPin, Phone, Star, Search, Users } from "lucide-react"
import { mockRestaurants, type Restaurant } from "@/lib/mock-data"
import Link from "next/link"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

export default function TouristBrowse() {
  const { user, isLoading } = useAuth()
  const router = useRouter()
  const [restaurants, setRestaurants] = useState<Restaurant[]>(mockRestaurants)
  const [searchQuery, setSearchQuery] = useState("")
  const [cuisineFilter, setCuisineFilter] = useState("all")

  useEffect(() => {
    if (!isLoading && (!user || (user.role !== "tourist" && user.role !== "customer"))) {
      router.push("/")
    }
  }, [user, isLoading, router])

  if (isLoading || !user) {
    return null
  }

  const filteredRestaurants = restaurants.filter((restaurant) => {
    const matchesSearch =
      restaurant.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      restaurant.description.toLowerCase().includes(searchQuery.toLowerCase())
    const matchesCuisine = cuisineFilter === "all" || restaurant.cuisine === cuisineFilter
    return matchesSearch && matchesCuisine
  })

  return (
    <div className="min-h-screen bg-gradient-to-b from-orange-50/30 via-white to-amber-50/30">
      <TouristNav active="browse" />

      <main className="container mx-auto px-4 py-8">
        {/* Hero Section */}
        <div className="text-center mb-12">
          <h1 className="text-5xl font-bold mb-4 text-balance bg-gradient-to-r from-orange-600 to-amber-600 bg-clip-text text-transparent">
            Discover Amazing Restaurants
          </h1>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto text-pretty">
            Browse through our curated selection of the finest dining experiences in the city
          </p>
        </div>

        {/* Search and Filter */}
        <Card className="mb-8 shadow-lg border-0 ring-1 ring-black/5">
          <CardContent className="pt-6">
            <div className="flex flex-col sm:flex-row gap-4">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-5 h-5" />
                <Input
                  placeholder="Search restaurants by name or description..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-11 h-12 text-base"
                />
              </div>
              <Select value={cuisineFilter} onValueChange={setCuisineFilter}>
                <SelectTrigger className="w-full sm:w-48 h-12">
                  <SelectValue placeholder="Filter by cuisine" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Cuisines</SelectItem>
                  <SelectItem value="Italian">Italian</SelectItem>
                  <SelectItem value="Japanese">Japanese</SelectItem>
                  <SelectItem value="Indian">Indian</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </CardContent>
        </Card>

        {/* Restaurant Grid */}
        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
          {filteredRestaurants.map((restaurant) => (
            <Link href={`/tourist/restaurant/${restaurant.id}`} className="block" key={restaurant.id}>
              <Card className="overflow-hidden hover:shadow-2xl transition-all duration-300 border-0 ring-1 ring-black/5 hover:ring-primary/20 group">
                <div className="aspect-video relative bg-muted overflow-hidden">
                  <img
                    src={restaurant.image || "/placeholder.svg"}
                    alt={restaurant.name}
                    className="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300"
                  />
                  <div className="absolute top-4 right-4 bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-full flex items-center gap-1.5 shadow-lg">
                    <Star className="w-4 h-4 fill-amber-500 text-amber-500" />
                    <span className="font-semibold text-sm">{restaurant.rating}</span>
                  </div>
                  <Badge className="absolute bottom-4 left-4 bg-white/95 text-foreground backdrop-blur-sm shadow-lg">
                    {restaurant.cuisine}
                  </Badge>
                </div>

                <CardHeader className="pb-3">
                  <div className="flex items-start justify-between gap-2">
                    <CardTitle className="text-2xl text-balance">{restaurant.name}</CardTitle>
                    <span className="text-lg font-semibold text-primary whitespace-nowrap">
                      {restaurant.priceRange}
                    </span>
                  </div>
                  <CardDescription className="text-base text-pretty leading-relaxed">
                    {restaurant.description}
                  </CardDescription>
                </CardHeader>

                <CardContent className="space-y-3">
                  <div className="flex items-start gap-2 text-sm text-muted-foreground">
                    <MapPin className="w-4 h-4 mt-0.5 flex-shrink-0 text-primary" />
                    <span>{restaurant.address}</span>
                  </div>
                  <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <Phone className="w-4 h-4 flex-shrink-0 text-primary" />
                    <span>{restaurant.phone}</span>
                  </div>
                  <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <Users className="w-4 h-4 flex-shrink-0 text-primary" />
                    <span>Capacity: {restaurant.seatingCapacity} seats</span>
                  </div>

                  <Button className="w-full mt-4 h-11 text-base font-medium bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700">
                    View Menu & Book Table
                  </Button>
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>

        {filteredRestaurants.length === 0 && (
          <Card className="border-0 ring-1 ring-black/5">
            <CardContent className="py-16 text-center">
              <Search className="w-16 h-16 text-muted-foreground mx-auto mb-4" />
              <h3 className="text-2xl font-semibold mb-2">No restaurants found</h3>
              <p className="text-muted-foreground text-lg">Try adjusting your search or filters</p>
            </CardContent>
          </Card>
        )}
      </main>
    </div>
  )
}
