"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { useAuth } from "@/lib/auth-context"
import { useRouter } from "next/navigation"
import { UtensilsCrossed, User, UserCog, Users } from "lucide-react"

export function LoginForm() {
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState("")
  const { login } = useAuth()
  const router = useRouter()

  const handleLogin = async (role: "admin" | "manager" | "customer" | "tourist") => {
    console.log("[v0] Handle login called with role:", role)

    if (!email || !password) {
      setError("Please enter both email and password")
      return
    }

    setIsLoading(true)
    setError("")

    try {
      console.log("[v0] Calling login function...")
      const success = await login(email, password, role)
      console.log("[v0] Login result:", success)

      if (success) {
        // Redirect based on role: tourist and customer go to tourist page, admin and manager go to admin page
        const redirectPath = role === "tourist" || role === "customer" ? "/tourist" : "/admin"
        console.log("[v0] Redirecting to:", redirectPath)
        router.push(redirectPath)
      } else {
        setError("Login failed. Please check your credentials.")
      }
    } catch (error) {
      console.error("[v0] Login error:", error)
      setError("An error occurred during login. Please try again.")
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-50 via-white to-amber-50 p-4">
      <Card className="w-full max-w-md shadow-xl border-0 ring-1 ring-black/5">
        <CardHeader className="space-y-3 text-center pb-6">
          <div className="mx-auto w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl flex items-center justify-center shadow-lg">
            <UtensilsCrossed className="w-8 h-8 text-white" />
          </div>
          <CardTitle className="text-3xl font-bold bg-gradient-to-r from-orange-600 to-amber-600 bg-clip-text text-transparent">
            Restaurant Manager
          </CardTitle>
          <CardDescription className="text-base">Sign in to access your dashboard or book a table</CardDescription>
        </CardHeader>
        <CardContent>
          <Tabs defaultValue="tourist" className="w-full">
            <TabsList className="grid w-full grid-cols-2 mb-6">
              <TabsTrigger value="tourist" className="gap-2">
                <User className="w-4 h-4" />
                Tourist/Customer
              </TabsTrigger>
              <TabsTrigger value="admin" className="gap-2">
                <UtensilsCrossed className="w-4 h-4" />
                Admin/Manager
              </TabsTrigger>
            </TabsList>

            {error && (
              <div className="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{error}</div>
            )}

            <TabsContent value="tourist" className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="tourist-email">Email</Label>
                <Input
                  id="tourist-email"
                  type="email"
                  placeholder="tourist@example.com"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="h-11"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="tourist-password">Password</Label>
                <Input
                  id="tourist-password"
                  type="password"
                  placeholder="Enter your password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="h-11"
                />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Button
                  onClick={() => handleLogin("tourist")}
                  disabled={isLoading}
                  variant="outline"
                  className="h-11 font-medium"
                >
                  <User className="w-4 h-4 mr-2" />
                  {isLoading ? "Loading..." : "Tourist"}
                </Button>
                <Button
                  onClick={() => handleLogin("customer")}
                  disabled={isLoading}
                  className="h-11 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white font-medium"
                >
                  <Users className="w-4 h-4 mr-2" />
                  {isLoading ? "Loading..." : "Customer"}
                </Button>
              </div>
            </TabsContent>

            <TabsContent value="admin" className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="admin-email">Email</Label>
                <Input
                  id="admin-email"
                  type="email"
                  placeholder="admin@restaurant.com"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="h-11"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="admin-password">Password</Label>
                <Input
                  id="admin-password"
                  type="password"
                  placeholder="Enter your password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="h-11"
                />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Button
                  onClick={() => handleLogin("manager")}
                  disabled={isLoading}
                  variant="outline"
                  className="h-11 font-medium"
                >
                  <UserCog className="w-4 h-4 mr-2" />
                  {isLoading ? "Loading..." : "Manager"}
                </Button>
                <Button
                  onClick={() => handleLogin("admin")}
                  disabled={isLoading}
                  className="h-11 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white font-medium"
                >
                  <UtensilsCrossed className="w-4 h-4 mr-2" />
                  {isLoading ? "Loading..." : "Admin"}
                </Button>
              </div>
            </TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  )
}
