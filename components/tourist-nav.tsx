"use client"

import { Button } from "@/components/ui/button"
import { useAuth } from "@/lib/auth-context"
import { useRouter } from "next/navigation"
import { UtensilsCrossed, Home, Calendar, Sparkles, LogOut, MapPin } from "lucide-react"
import Link from "next/link"

export function TouristNav({ active }: { active: "browse" | "bookings" | "services" | "places" }) {
  const { logout, user } = useAuth()
  const router = useRouter()

  const handleLogout = () => {
    logout()
    router.push("/")
  }

  return (
    <nav className="border-b bg-card sticky top-0 z-50 backdrop-blur-sm bg-card/95">
      <div className="container mx-auto px-4">
        <div className="flex h-16 items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center">
              <UtensilsCrossed className="w-6 h-6 text-white" />
            </div>
            <div>
              <h1 className="font-bold text-lg">Restaurant Finder</h1>
              <p className="text-xs text-muted-foreground">Welcome, {user?.name}</p>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <Link href="/tourist/places">
              <Button variant={active === "places" ? "default" : "ghost"} size="sm" className="gap-2">
                <MapPin className="w-4 h-4" />
                Places
              </Button>
            </Link>
            <Link href="/tourist">
              <Button variant={active === "browse" ? "default" : "ghost"} size="sm" className="gap-2">
                <Home className="w-4 h-4" />
                Browse
              </Button>
            </Link>
            <Link href="/tourist/bookings">
              <Button variant={active === "bookings" ? "default" : "ghost"} size="sm" className="gap-2">
                <Calendar className="w-4 h-4" />
                My Bookings
              </Button>
            </Link>
            <Link href="/tourist/services">
              <Button variant={active === "services" ? "default" : "ghost"} size="sm" className="gap-2">
                <Sparkles className="w-4 h-4" />
                Services
              </Button>
            </Link>
            <Button variant="ghost" size="sm" onClick={handleLogout} className="gap-2 text-destructive">
              <LogOut className="w-4 h-4" />
              Logout
            </Button>
          </div>
        </div>
      </div>
    </nav>
  )
}
