"use client"

import { Button } from "@/components/ui/button"
import { useAuth } from "@/lib/auth-context"
import { useRouter } from "next/navigation"
import { LayoutDashboard, UtensilsCrossed, Calendar, BarChart3, LogOut, User } from "lucide-react"
import Link from "next/link"

export function AdminNav({ active }: { active: "dashboard" | "menu" | "reservations" | "analytics" | "profile" }) {
  const { logout, user } = useAuth()
  const router = useRouter()

  const handleLogout = () => {
    logout()
    router.push("/")
  }

  return (
    <nav className="border-b bg-card">
      <div className="container mx-auto px-4">
        <div className="flex h-16 items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center">
              <UtensilsCrossed className="w-6 h-6 text-white" />
            </div>
            <div>
              <h1 className="font-bold text-lg">Restaurant Manager</h1>
              <p className="text-xs text-muted-foreground">{user?.name}</p>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <Link href="/admin">
              <Button variant={active === "dashboard" ? "default" : "ghost"} size="sm" className="gap-2">
                <LayoutDashboard className="w-4 h-4" />
                Dashboard
              </Button>
            </Link>
            <Link href="/admin/menu">
              <Button variant={active === "menu" ? "default" : "ghost"} size="sm" className="gap-2">
                <UtensilsCrossed className="w-4 h-4" />
                Menu
              </Button>
            </Link>
            <Link href="/admin/reservations">
              <Button variant={active === "reservations" ? "default" : "ghost"} size="sm" className="gap-2">
                <Calendar className="w-4 h-4" />
                Reservations
              </Button>
            </Link>
            <Link href="/admin/analytics">
              <Button variant={active === "analytics" ? "default" : "ghost"} size="sm" className="gap-2">
                <BarChart3 className="w-4 h-4" />
                Analytics
              </Button>
            </Link>
            <Link href="/admin/profile">
              <Button variant={active === "profile" ? "default" : "ghost"} size="sm" className="gap-2">
                <User className="w-4 h-4" />
                Profile
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
