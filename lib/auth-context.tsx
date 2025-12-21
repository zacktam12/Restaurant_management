"use client"

import { createContext, useContext, useState, useEffect, type ReactNode } from "react"

type User = {
  id: string
  email: string
  name: string
  role: "admin" | "manager" | "customer" | "tourist"
  restaurantId?: string
  phone?: string
  professionalDetails?: string
}

type AuthContextType = {
  user: User | null
  login: (email: string, password: string, role: "admin" | "manager" | "customer" | "tourist") => Promise<boolean>
  logout: () => void
  isLoading: boolean
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    // Check for existing session
    const savedUser = localStorage.getItem("user")
    if (savedUser) {
      setUser(JSON.parse(savedUser))
    }
    setIsLoading(false)
  }, [])

  const login = async (email: string, password: string, role: "admin" | "manager" | "customer" | "tourist") => {
    console.log("[v0] Login attempt - Email:", email, "Role:", role)

    // Mock authentication - replace with real API call
    await new Promise((resolve) => setTimeout(resolve, 500))

    const mockUser: User = {
      id: Math.random().toString(36).substr(2, 9),
      email,
      name: email.split("@")[0],
      role,
      restaurantId: role === "admin" || role === "manager" ? "rest-1" : undefined,
      phone:
        role === "admin"
          ? "+1-555-0123"
          : role === "manager"
            ? "+1-555-0456"
            : role === "customer"
              ? "+1-555-0789"
              : "+1-555-0999",
      professionalDetails:
        role === "admin"
          ? "Restaurant Manager with 10+ years experience"
          : role === "manager"
            ? "Professional Chef with 5+ years experience"
            : undefined,
    }

    console.log("[v0] Login successful - User:", mockUser)
    setUser(mockUser)
    localStorage.setItem("user", JSON.stringify(mockUser))
    return true
  }

  const logout = () => {
    setUser(null)
    localStorage.removeItem("user")
  }

  return <AuthContext.Provider value={{ user, login, logout, isLoading }}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (context === undefined) {
    throw new Error("useAuth must be used within an AuthProvider")
  }
  return context
}
